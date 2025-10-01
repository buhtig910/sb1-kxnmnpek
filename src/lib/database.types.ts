export type Json =
  | string
  | number
  | boolean
  | null
  | { [key: string]: Json | undefined }
  | Json[]

export interface Database {
  public: {
    Tables: {
      profiles: {
        Row: {
          id: string
          email: string
          full_name: string | null
          balance: number
          created_at: string
          updated_at: string
        }
        Insert: {
          id: string
          email: string
          full_name?: string | null
          balance?: number
          created_at?: string
          updated_at?: string
        }
        Update: {
          id?: string
          email?: string
          full_name?: string | null
          balance?: number
          created_at?: string
          updated_at?: string
        }
      }
      trades: {
        Row: {
          id: string
          user_id: string
          symbol: string
          trade_type: 'buy' | 'sell'
          quantity: number
          price: number
          total_amount: number
          status: 'pending' | 'completed' | 'cancelled'
          executed_at: string | null
          created_at: string
        }
        Insert: {
          id?: string
          user_id: string
          symbol: string
          trade_type: 'buy' | 'sell'
          quantity: number
          price: number
          total_amount: number
          status?: 'pending' | 'completed' | 'cancelled'
          executed_at?: string | null
          created_at?: string
        }
        Update: {
          id?: string
          user_id?: string
          symbol?: string
          trade_type?: 'buy' | 'sell'
          quantity?: number
          price?: number
          total_amount?: number
          status?: 'pending' | 'completed' | 'cancelled'
          executed_at?: string | null
          created_at?: string
        }
      }
      positions: {
        Row: {
          id: string
          user_id: string
          symbol: string
          quantity: number
          average_price: number
          created_at: string
          updated_at: string
        }
        Insert: {
          id?: string
          user_id: string
          symbol: string
          quantity?: number
          average_price: number
          created_at?: string
          updated_at?: string
        }
        Update: {
          id?: string
          user_id?: string
          symbol?: string
          quantity?: number
          average_price?: number
          created_at?: string
          updated_at?: string
        }
      }
    }
    Views: Record<string, never>
    Functions: Record<string, never>
    Enums: Record<string, never>
  }
}
