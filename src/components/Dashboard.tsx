import { useState } from 'react';
import { useAuth } from '../contexts/AuthContext';
import { useProfile } from '../hooks/useProfile';
import { usePositions } from '../hooks/usePositions';
import { useTrades } from '../hooks/useTrades';
import PortfolioSummary from './PortfolioSummary';
import PositionsList from './PositionsList';
import TradeForm from './TradeForm';
import TradeHistory from './TradeHistory';
import Header from './Header';

export default function Dashboard() {
  const { user } = useAuth();
  const { profile, loading: profileLoading } = useProfile();
  const { positions, loading: positionsLoading, refetch: refetchPositions } = usePositions();
  const { trades, loading: tradesLoading, refetch: refetchTrades } = useTrades();
  const [activeTab, setActiveTab] = useState<'trade' | 'positions' | 'history'>('trade');

  const handleTradeComplete = () => {
    refetchPositions();
    refetchTrades();
  };

  if (profileLoading || positionsLoading || tradesLoading) {
    return (
      <div className="min-h-screen bg-slate-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-600 mx-auto"></div>
          <p className="mt-4 text-gray-600">Loading your portfolio...</p>
        </div>
      </div>
    );
  }

  if (!profile) {
    return (
      <div className="min-h-screen bg-slate-50 flex items-center justify-center">
        <div className="text-center">
          <p className="text-gray-600">Profile not found</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-slate-50">
      <Header profile={profile} />

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <PortfolioSummary profile={profile} positions={positions} />

        <div className="mt-8">
          <div className="border-b border-gray-200 mb-6">
            <nav className="-mb-px flex space-x-8">
              <button
                onClick={() => setActiveTab('trade')}
                className={`py-4 px-1 border-b-2 font-medium text-sm transition-colors ${
                  activeTab === 'trade'
                    ? 'border-emerald-600 text-emerald-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                }`}
              >
                Trade
              </button>
              <button
                onClick={() => setActiveTab('positions')}
                className={`py-4 px-1 border-b-2 font-medium text-sm transition-colors ${
                  activeTab === 'positions'
                    ? 'border-emerald-600 text-emerald-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                }`}
              >
                Positions
              </button>
              <button
                onClick={() => setActiveTab('history')}
                className={`py-4 px-1 border-b-2 font-medium text-sm transition-colors ${
                  activeTab === 'history'
                    ? 'border-emerald-600 text-emerald-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                }`}
              >
                History
              </button>
            </nav>
          </div>

          <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            {activeTab === 'trade' && (
              <TradeForm
                userId={user!.id}
                balance={profile.balance}
                positions={positions}
                onTradeComplete={handleTradeComplete}
              />
            )}
            {activeTab === 'positions' && <PositionsList positions={positions} />}
            {activeTab === 'history' && <TradeHistory trades={trades} />}
          </div>
        </div>
      </main>
    </div>
  );
}
