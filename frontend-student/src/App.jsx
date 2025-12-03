import React from 'react'
import { Routes, Route, Navigate, Link, useNavigate, useLocation } from 'react-router-dom'
import Courses from './pages/Courses'
import Blocks from './pages/Blocks'
import Answer from './pages/Answer'
import Login from './pages/Login'
import { AuthProvider, useAuth } from './auth/AuthProvider'

function RequireAuth({ children }) {
  const { user, loading } = useAuth()
  if (loading) return <div>Loading session...</div>
  if (!user) return <Navigate to="/login" replace />
  return children
}

export default function App() {
  return (
    <AuthProvider>
      <div className="app">
        <header className="header">
          <HeaderBar />
        </header>
        <main className="container" role="main">
          <Routes>
            <Route path="/login" element={<Login />} />
            <Route path="/" element={<Navigate to="/courses" replace />} />
            <Route path="/courses" element={<RequireAuth><Courses /></RequireAuth>} />
            <Route path="/courses/:courseId/blocks" element={<RequireAuth><Blocks /></RequireAuth>} />
            <Route path="/courses/:courseId/blocks/:blockId/answer/:answerId" element={<RequireAuth><Answer /></RequireAuth>} />
          </Routes>
        </main>
      </div>
    </AuthProvider>
  )
}

function HeaderBar() {
  const { user, logout } = useAuth()
  const navigate = useNavigate()
  const location = useLocation()

  async function handleLogout() {
    try {
      await logout()
    } catch (e) {
      console.error('Logout failed', e)
    } finally {
      navigate('/login', { replace: true })
    }
  }

  const displayName = user
    ? (() => {
        const wrapper = user.result || user
        const last = (wrapper.lastName || wrapper.surname || '').toString().trim()
        const first = (wrapper.name || wrapper.firstName || wrapper.first || '').toString().trim()
        const patron = (wrapper.patronymic || wrapper.middleName || '').toString().trim()
        const parts = [last, first, patron].filter(Boolean)
        if (parts.length) return parts.join(' ')
        return (wrapper.fullName || wrapper.name || wrapper.login || '').toString().trim()
      })()
    : ''

  const showActions = location && location.pathname !== '/login'

  return (
    <div className="header-inner">
      <Link to="/courses" className="logo">MiniLMS</Link>
      {showActions && (
        <div className="header-actions">
          <div className="user-name">{displayName}</div>
          <button className="btn secondary" onClick={handleLogout}>Выйти</button>
        </div>
      )}
    </div>
  )
}
