import { useState, useEffect } from 'react';
import { Search, TrendingUp, TrendingDown, AlertCircle } from 'lucide-react';
import { executeTrade } from '../services/tradeService';
import { searchStocks, subscribeToPrice } from '../services/marketData';
import type { Database } from '../lib/database.types';

type Position = Database['public']['Tables']['positions']['Row'];

interface TradeFormProps {
  userId: string;
  balance: number;
  positions: Position[];
  onTradeComplete: () => void;
}

export default function TradeForm({ userId, balance, positions, onTradeComplete }: TradeFormProps) {
  const [tradeType, setTradeType] = useState<'buy' | 'sell'>('buy');
  const [symbol, setSymbol] = useState('');
  const [quantity, setQuantity] = useState('');
  const [searchResults, setSearchResults] = useState<string[]>([]);
  const [currentPrice, setCurrentPrice] = useState<number | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [showSearch, setShowSearch] = useState(false);

  useEffect(() => {
    if (symbol.length >= 1) {
      const search = async () => {
        const results = await searchStocks(symbol);
        setSearchResults(results);
      };
      search();
    } else {
      setSearchResults([]);
    }
  }, [symbol]);

  useEffect(() => {
    if (symbol && symbol.length >= 2) {
      const unsubscribe = subscribeToPrice(symbol.toUpperCase(), (quote) => {
        setCurrentPrice(quote.price);
      });
      return unsubscribe;
    } else {
      setCurrentPrice(null);
    }
  }, [symbol]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setSuccess('');

    if (!symbol || !quantity) {
      setError('Please fill in all fields');
      return;
    }

    const qty = parseFloat(quantity);
    if (isNaN(qty) || qty <= 0) {
      setError('Please enter a valid quantity');
      return;
    }

    setLoading(true);

    try {
      await executeTrade({
        userId,
        symbol: symbol.toUpperCase(),
        tradeType,
        quantity: qty,
      });

      setSuccess(`Successfully ${tradeType === 'buy' ? 'bought' : 'sold'} ${qty} shares of ${symbol.toUpperCase()}`);
      setSymbol('');
      setQuantity('');
      setCurrentPrice(null);
      onTradeComplete();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Trade failed');
    } finally {
      setLoading(false);
    }
  };

  const estimatedTotal = currentPrice && quantity ? currentPrice * parseFloat(quantity) : 0;
  const position = positions.find(p => p.symbol === symbol.toUpperCase());

  return (
    <div>
      <div className="mb-6">
        <h2 className="text-2xl font-bold text-gray-900 mb-2">Execute Trade</h2>
        <p className="text-gray-600">Buy or sell stocks with real-time pricing</p>
      </div>

      <form onSubmit={handleSubmit} className="space-y-6">
        <div className="flex gap-4">
          <button
            type="button"
            onClick={() => setTradeType('buy')}
            className={`flex-1 py-3 px-4 rounded-lg font-semibold transition-all ${
              tradeType === 'buy'
                ? 'bg-emerald-600 text-white shadow-lg'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`}
          >
            <TrendingUp className="inline-block mr-2 h-5 w-5" />
            Buy
          </button>
          <button
            type="button"
            onClick={() => setTradeType('sell')}
            className={`flex-1 py-3 px-4 rounded-lg font-semibold transition-all ${
              tradeType === 'sell'
                ? 'bg-red-600 text-white shadow-lg'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`}
          >
            <TrendingDown className="inline-block mr-2 h-5 w-5" />
            Sell
          </button>
        </div>

        <div className="relative">
          <label htmlFor="symbol" className="block text-sm font-medium text-gray-700 mb-2">
            Stock Symbol
          </label>
          <div className="relative">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
            <input
              id="symbol"
              type="text"
              value={symbol}
              onChange={(e) => {
                setSymbol(e.target.value.toUpperCase());
                setShowSearch(true);
              }}
              onFocus={() => setShowSearch(true)}
              className="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition duration-200"
              placeholder="e.g., AAPL"
              required
            />
          </div>

          {showSearch && searchResults.length > 0 && (
            <div className="absolute z-10 w-full mt-2 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
              {searchResults.map((result) => (
                <button
                  key={result}
                  type="button"
                  onClick={() => {
                    setSymbol(result);
                    setShowSearch(false);
                  }}
                  className="w-full text-left px-4 py-3 hover:bg-gray-50 transition-colors border-b border-gray-100 last:border-b-0"
                >
                  <span className="font-semibold text-gray-900">{result}</span>
                </button>
              ))}
            </div>
          )}

          {currentPrice && (
            <div className="mt-2 p-3 bg-gray-50 rounded-lg">
              <p className="text-sm text-gray-600">Current Price: <span className="font-bold text-gray-900">${currentPrice.toFixed(2)}</span></p>
            </div>
          )}

          {tradeType === 'sell' && position && (
            <div className="mt-2 p-3 bg-blue-50 rounded-lg border border-blue-200">
              <p className="text-sm text-blue-800">
                You own <span className="font-bold">{position.quantity}</span> shares at avg. ${position.average_price.toFixed(2)}
              </p>
            </div>
          )}
        </div>

        <div>
          <label htmlFor="quantity" className="block text-sm font-medium text-gray-700 mb-2">
            Quantity
          </label>
          <input
            id="quantity"
            type="number"
            value={quantity}
            onChange={(e) => setQuantity(e.target.value)}
            className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition duration-200"
            placeholder="Number of shares"
            min="0.0001"
            step="0.0001"
            required
          />
        </div>

        {estimatedTotal > 0 && (
          <div className="p-4 bg-gray-50 rounded-lg border border-gray-200">
            <div className="flex justify-between items-center">
              <span className="text-sm font-medium text-gray-700">Estimated Total:</span>
              <span className="text-xl font-bold text-gray-900">
                ${estimatedTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
              </span>
            </div>
            {tradeType === 'buy' && (
              <p className="text-xs text-gray-500 mt-2">
                Remaining balance: ${(balance - estimatedTotal).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
              </p>
            )}
          </div>
        )}

        {error && (
          <div className="flex items-start gap-2 p-4 bg-red-50 border border-red-200 rounded-lg">
            <AlertCircle className="h-5 w-5 text-red-600 flex-shrink-0 mt-0.5" />
            <p className="text-sm text-red-800">{error}</p>
          </div>
        )}

        {success && (
          <div className="p-4 bg-emerald-50 border border-emerald-200 rounded-lg">
            <p className="text-sm text-emerald-800">{success}</p>
          </div>
        )}

        <button
          type="submit"
          disabled={loading || !currentPrice}
          className={`w-full py-3 rounded-lg font-semibold transition-all shadow-lg ${
            tradeType === 'buy'
              ? 'bg-emerald-600 hover:bg-emerald-700 text-white'
              : 'bg-red-600 hover:bg-red-700 text-white'
          } disabled:opacity-50 disabled:cursor-not-allowed`}
        >
          {loading ? 'Processing...' : tradeType === 'buy' ? 'Buy Shares' : 'Sell Shares'}
        </button>
      </form>
    </div>
  );
}
