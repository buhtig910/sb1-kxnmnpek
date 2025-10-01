import { useState, useEffect } from 'react';
import { TrendingUp, TrendingDown } from 'lucide-react';
import { getStockQuote } from '../services/marketData';
import type { Database } from '../lib/database.types';

type Position = Database['public']['Tables']['positions']['Row'];

interface PositionWithPrice extends Position {
  currentPrice?: number;
  gainLoss?: number;
  gainLossPercent?: number;
  marketValue?: number;
}

interface PositionsListProps {
  positions: Position[];
}

export default function PositionsList({ positions }: PositionsListProps) {
  const [enrichedPositions, setEnrichedPositions] = useState<PositionWithPrice[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (positions.length === 0) {
      setEnrichedPositions([]);
      setLoading(false);
      return;
    }

    const enrichPositions = async () => {
      const enriched = await Promise.all(
        positions.map(async (position) => {
          try {
            const quote = await getStockQuote(position.symbol);
            const currentPrice = quote.price;
            const marketValue = currentPrice * position.quantity;
            const costBasis = position.average_price * position.quantity;
            const gainLoss = marketValue - costBasis;
            const gainLossPercent = (gainLoss / costBasis) * 100;

            return {
              ...position,
              currentPrice,
              marketValue,
              gainLoss,
              gainLossPercent,
            };
          } catch (error) {
            return position;
          }
        })
      );
      setEnrichedPositions(enriched);
      setLoading(false);
    };

    enrichPositions();
    const interval = setInterval(enrichPositions, 5000);
    return () => clearInterval(interval);
  }, [positions]);

  if (loading) {
    return (
      <div className="flex items-center justify-center py-12">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-600"></div>
      </div>
    );
  }

  if (enrichedPositions.length === 0) {
    return (
      <div className="text-center py-12">
        <TrendingUp className="h-12 w-12 text-gray-400 mx-auto mb-4" />
        <h3 className="text-lg font-semibold text-gray-900 mb-2">No Positions Yet</h3>
        <p className="text-gray-600">Start trading to build your portfolio</p>
      </div>
    );
  }

  return (
    <div>
      <h2 className="text-2xl font-bold text-gray-900 mb-6">Your Positions</h2>
      <div className="overflow-x-auto">
        <table className="w-full">
          <thead>
            <tr className="border-b border-gray-200">
              <th className="text-left py-3 px-4 font-semibold text-gray-700">Symbol</th>
              <th className="text-right py-3 px-4 font-semibold text-gray-700">Quantity</th>
              <th className="text-right py-3 px-4 font-semibold text-gray-700">Avg. Price</th>
              <th className="text-right py-3 px-4 font-semibold text-gray-700">Current Price</th>
              <th className="text-right py-3 px-4 font-semibold text-gray-700">Market Value</th>
              <th className="text-right py-3 px-4 font-semibold text-gray-700">Gain/Loss</th>
            </tr>
          </thead>
          <tbody>
            {enrichedPositions.map((position) => (
              <tr
                key={position.id}
                className="border-b border-gray-100 hover:bg-gray-50 transition-colors"
              >
                <td className="py-4 px-4">
                  <span className="font-bold text-gray-900">{position.symbol}</span>
                </td>
                <td className="py-4 px-4 text-right text-gray-900">
                  {position.quantity.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 4 })}
                </td>
                <td className="py-4 px-4 text-right text-gray-900">
                  ${position.average_price.toFixed(2)}
                </td>
                <td className="py-4 px-4 text-right text-gray-900">
                  {position.currentPrice ? `$${position.currentPrice.toFixed(2)}` : '...'}
                </td>
                <td className="py-4 px-4 text-right text-gray-900 font-semibold">
                  {position.marketValue
                    ? `$${position.marketValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
                    : '...'}
                </td>
                <td className="py-4 px-4 text-right">
                  {position.gainLoss !== undefined && position.gainLossPercent !== undefined ? (
                    <div className="flex items-center justify-end gap-2">
                      <div className={position.gainLoss >= 0 ? 'text-emerald-600' : 'text-red-600'}>
                        <div className="font-semibold">
                          {position.gainLoss >= 0 ? '+' : ''}
                          ${Math.abs(position.gainLoss).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                        </div>
                        <div className="text-sm flex items-center justify-end gap-1">
                          {position.gainLoss >= 0 ? (
                            <TrendingUp className="h-3 w-3" />
                          ) : (
                            <TrendingDown className="h-3 w-3" />
                          )}
                          {position.gainLoss >= 0 ? '+' : ''}
                          {position.gainLossPercent.toFixed(2)}%
                        </div>
                      </div>
                    </div>
                  ) : (
                    '...'
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
