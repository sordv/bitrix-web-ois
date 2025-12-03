import React, { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../auth/AuthProvider'

export default function Login() {
  const [login, setLogin] = useState('')
  const [password, setPassword] = useState('')
  const [loading, setLoading] = useState(false)
  const auth = useAuth()
  const navigate = useNavigate()

  async function onSubmit(e) {
    e.preventDefault()
    setLoading(true)
    try {
      await auth.login({ login, password })
      navigate('/courses', { replace: true })
    } catch (err) {
      console.error(err)
      alert('Login failed')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="centered-wrap">
      <div className="login-card">
        <h2 className="login-title">Вход в MiniLMS</h2>
        <form onSubmit={onSubmit} className="login-form">
          <label className="login-label">
            <span className="label-text">Логин</span>
            <input className="login-input" value={login} onChange={e => setLogin(e.target.value)} placeholder="Введите логин" />
          </label>

          <label className="login-label">
            <span className="label-text">Пароль</span>
            <input className="login-input" type="password" value={password} onChange={e => setPassword(e.target.value)} placeholder="Введите пароль" />
          </label>

          <button className="btn" disabled={loading} type="submit">{loading ? 'Вход...' : 'Войти'}</button>
        </form>
      </div>
    </div>
  )
}
