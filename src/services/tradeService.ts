import { supabase } from '../lib/supabase';
import { getStockQuote } from './marketData';

interface ExecuteTradeParams {
  userId: string;
  symbol: string;
  tradeType: 'buy' | 'sell';
  quantity: number;
}

export async function executeTrade({
  userId,
  symbol,
  tradeType,
  quantity
}: ExecuteTradeParams) {
  const quote = await getStockQuote(symbol);
  const price = quote.price;
  const totalAmount = price * quantity;

  const { data: profile } = await supabase
    .from('profiles')
    .select('balance')
    .eq('id', userId)
    .maybeSingle();

  if (!profile) {
    throw new Error('Profile not found');
  }

  if (tradeType === 'buy' && profile.balance < totalAmount) {
    throw new Error('Insufficient balance');
  }

  if (tradeType === 'sell') {
    const { data: position } = await supabase
      .from('positions')
      .select('quantity')
      .eq('user_id', userId)
      .eq('symbol', symbol)
      .maybeSingle();

    if (!position || position.quantity < quantity) {
      throw new Error('Insufficient shares to sell');
    }
  }

  const { data: trade, error: tradeError } = await supabase
    .from('trades')
    .insert({
      user_id: userId,
      symbol,
      trade_type: tradeType,
      quantity,
      price,
      total_amount: totalAmount,
      status: 'completed',
      executed_at: new Date().toISOString(),
    })
    .select()
    .single();

  if (tradeError) throw tradeError;

  if (tradeType === 'buy') {
    const { data: existingPosition } = await supabase
      .from('positions')
      .select('*')
      .eq('user_id', userId)
      .eq('symbol', symbol)
      .maybeSingle();

    if (existingPosition) {
      const newQuantity = existingPosition.quantity + quantity;
      const newAveragePrice =
        (existingPosition.average_price * existingPosition.quantity + totalAmount) / newQuantity;

      const { error: updateError } = await supabase
        .from('positions')
        .update({
          quantity: newQuantity,
          average_price: newAveragePrice,
          updated_at: new Date().toISOString(),
        })
        .eq('id', existingPosition.id);

      if (updateError) throw updateError;
    } else {
      const { error: insertError } = await supabase.from('positions').insert({
        user_id: userId,
        symbol,
        quantity,
        average_price: price,
      });

      if (insertError) throw insertError;
    }

    const { error: balanceError } = await supabase
      .from('profiles')
      .update({
        balance: profile.balance - totalAmount,
        updated_at: new Date().toISOString()
      })
      .eq('id', userId);

    if (balanceError) throw balanceError;
  } else {
    const { data: position, error: positionError } = await supabase
      .from('positions')
      .select('*')
      .eq('user_id', userId)
      .eq('symbol', symbol)
      .maybeSingle();

    if (positionError || !position) {
      throw new Error('Position not found');
    }

    const newQuantity = position.quantity - quantity;

    if (newQuantity < 0) {
      throw new Error('Cannot sell more shares than you own');
    }

    if (newQuantity === 0) {
      const { error: deleteError } = await supabase
        .from('positions')
        .delete()
        .eq('id', position.id);

      if (deleteError) throw deleteError;
    } else {
      const { error: updateError } = await supabase
        .from('positions')
        .update({
          quantity: newQuantity,
          updated_at: new Date().toISOString(),
        })
        .eq('id', position.id);

      if (updateError) throw updateError;
    }

    const { error: balanceError } = await supabase
      .from('profiles')
      .update({
        balance: profile.balance + totalAmount,
        updated_at: new Date().toISOString()
      })
      .eq('id', userId);

    if (balanceError) throw balanceError;
  }

  return trade;
}
