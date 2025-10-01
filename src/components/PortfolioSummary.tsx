import { useState, useEffect } from 'react';
import { Wallet, TrendingUp, TrendingDown, DollarSign } from 'lucide-react';
import { getStockQuote } from '../services/marketData';
import type { Database } from '../lib/database.types';

type Profile = Database['public']['Tables']['profiles']['Row'];
type Position = Database['public']['Tables']['positions']['Row'];

interface PortfolioSummaryProps {
  profile: Profile;
  positions: Position[];
}

export default function PortfolioSummary({ profile, positions }: PortfolioSummaryProps) {
  const [portfolioValue, setPortfolioValue] = useState(0);
  const [totalGainLoss, setTotalGainLoss] = useState(0);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const calculatePortfolioValue = async () => {
      if (positions.length === 0) {
        setPortfolioValue(0);
        setTotalGainLoss(0);
        setLoading(false);
        return;
      }

      try {
        const values = await Promise.all(
          positions.map(async (position) => {
            const quote = await getStockQuote(position.symbol);
            const currentValue = quote.price * position.quantity;
            const costBasis = position.average_price * position.quantity;
            const gainLoss = currentValue - costBasis;
            return { currentValue, gainLoss };
          })
        );

        const totalValue = values.reduce((sum, v) => sum + v.currentValue, 0);
        const totalGL = values.reduce((sum, v) => sum + v.gainLoss, 0);

        setPortfolioValue(totalValue);
        setTotalGainLoss(totalGL);
      } catch (error) {
        console.error('Error calculating portfolio value:', error);
      } finally {
        setLoading(false);
      }
    };

    calculatePortfolioValue();
    const interval = setInterval(calculatePortfolioValue, 5000);
    return () => clearInterval(interval);
  }, [positions]);

  const totalValue = profile.balance + portfolioValue;
  const gainLossPercent = portfolioValue > 0 ? (totalGainLoss / (portfolioValue - totalGainLoss)) * 100 : 0;

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div className="flex items-center justify-between">
          <div>
            <p className="text-sm font-medium text-gray-600">Total Value</p>
            <p className="text-2xl font-bold text-gray-900 mt-2">
              ${totalValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
            </p>
          </div>
          <div className="bg-emerald-100 p-3 rounded-full">
            <DollarSign className="h-6 w-6 text-emerald-600" />
          </div>
        </div>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div className="flex items-center justify-between">
          <div>
            <p className="text-sm font-medium text-gray-600">Cash Balance</p>
            <p className="text-2xl font-bold text-gray-900 mt-2">
              ${profile.balance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
            </p>
          </div>
          <div className="bg-blue-100 p-3 rounded-full">
            <Wallet className="h-6 w-6 text-blue-600" />
          </div>
        </div>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div className="flex items-center justify-between">
          <div>
            <p className="text-sm font-medium text-gray-600">Portfolio Value</p>
            {loading ? (
              <p className="text-2xl font-bold text-gray-900 mt-2">...</p>
            ) : (
              <p className="text-2xl font-bold text-gray-900 mt-2">
                ${portfolioValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
              </p>
            )}
          </div>
          <div className="bg-amber-100 p-3 rounded-full">
            <TrendingUp className="h-6 w-6 text-amber-600" />
          </div>
        </div>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div className="flex items-center justify-between">
          <div>
            <p className="text-sm font-medium text-gray-600">Total Gain/Loss</p>
            {loading ? (
              <p className="text-2xl font-bold text-gray-900 mt-2">...</p>
            ) : (
              <>
                <p className={`text-2xl font-bold mt-2 ${totalGainLoss >= 0 ? 'text-emerald-600' : 'text-red-600'}`}>
                  {totalGainLoss >= 0 ? '+' : ''}${totalGainLoss.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                </p>
                <p className={`text-sm font-medium ${totalGainLoss >= 0 ? 'text-emerald-600' : 'text-red-600'}`}>
                  {totalGainLoss >= 0 ? '+' : ''}{gainLossPercent.toFixed(2)}%
                </p>
              </>
            )}
          </div>
          <div className={`p-3 rounded-full ${totalGainLoss >= 0 ? 'bg-emerald-100' : 'bg-red-100'}`}>
            {totalGainLoss >= 0 ? (
              <TrendingUp className="h-6 w-6 text-emerald-600" />
            ) : (
              <TrendingDown className="h-6 w-6 text-red-600" />
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
