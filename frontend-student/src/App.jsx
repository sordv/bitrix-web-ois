import React from 'react'
import { Routes, Route, Navigate, Link, useNavigate } from 'react-router-dom'
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
          <div className="header-inner">
            <Link to="/courses" className="logo">MiniLMS</Link>
          </div>
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
