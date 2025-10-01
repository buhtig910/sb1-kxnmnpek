import { TrendingUp, TrendingDown, Clock } from 'lucide-react';
import type { Database } from '../lib/database.types';

type Trade = Database['public']['Tables']['trades']['Row'];

interface TradeHistoryProps {
  trades: Trade[];
}

export default function TradeHistory({ trades }: TradeHistoryProps) {
  if (trades.length === 0) {
    return (
      <div className="text-center py-12">
        <Clock className="h-12 w-12 text-gray-400 mx-auto mb-4" />
        <h3 className="text-lg font-semibold text-gray-900 mb-2">No Trade History</h3>
        <p className="text-gray-600">Your completed trades will appear here</p>
      </div>
    );
  }

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  return (
    <div>
      <h2 className="text-2xl font-bold text-gray-900 mb-6">Trade History</h2>
      <div className="space-y-3">
        {trades.map((trade) => (
          <div
            key={trade.id}
            className="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
          >
            <div className="flex items-center gap-4">
              <div
                className={`p-2 rounded-full ${
                  trade.trade_type === 'buy' ? 'bg-emerald-100' : 'bg-red-100'
                }`}
              >
                {trade.trade_type === 'buy' ? (
                  <TrendingUp className="h-5 w-5 text-emerald-600" />
                ) : (
                  <TrendingDown className="h-5 w-5 text-red-600" />
                )}
              </div>
              <div>
                <div className="flex items-center gap-3">
                  <span className="font-bold text-gray-900">{trade.symbol}</span>
                  <span
                    className={`text-xs font-semibold px-2 py-1 rounded ${
                      trade.trade_type === 'buy'
                        ? 'bg-emerald-100 text-emerald-700'
                        : 'bg-red-100 text-red-700'
                    }`}
                  >
                    {trade.trade_type.toUpperCase()}
                  </span>
                  <span
                    className={`text-xs font-semibold px-2 py-1 rounded ${
                      trade.status === 'completed'
                        ? 'bg-gray-200 text-gray-700'
                        : trade.status === 'pending'
                        ? 'bg-yellow-100 text-yellow-700'
                        : 'bg-gray-300 text-gray-600'
                    }`}
                  >
                    {trade.status.toUpperCase()}
                  </span>
                </div>
                <p className="text-sm text-gray-600 mt-1">
                  {trade.quantity} shares @ ${trade.price.toFixed(2)}
                </p>
                <p className="text-xs text-gray-500 mt-1">
                  {trade.executed_at ? formatDate(trade.executed_at) : formatDate(trade.created_at)}
                </p>
              </div>
            </div>
            <div className="text-right">
              <p className="font-bold text-gray-900">
                ${trade.total_amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
              </p>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
