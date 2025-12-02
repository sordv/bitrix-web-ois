import React, { createContext, useContext, useEffect, useState } from 'react'
import { getUser as apiGetUser, login as apiLogin, logout as apiLogout } from '../api'

const AuthContext = createContext(null)

export function useAuth() {
  return useContext(AuthContext)
}

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    async function init() {
      try {
        const u = await apiGetUser()
        setUser(u || null)
      } catch (e) {
        setUser(null)
      } finally {
        setLoading(false)
      }
    }
    init()
  }, [])

  async function login(values) {
    await apiLogin(values)
    const u = await apiGetUser()
    setUser(u || null)
    return u
  }

  async function logout() {
    try {
      await apiLogout()
    } finally {
      setUser(null)
    }
  }

  const value = { user, loading, login, logout }
  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}
