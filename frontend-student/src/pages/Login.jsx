import React, { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../auth/AuthProvider'

export default function Login() {
  const [login, setLogin] = useState('student1')
  const [password, setPassword] = useState('student1')
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
      <h2>Login</h2>
      <form onSubmit={onSubmit} className="form-column">
        <label>
          Login
          <input value={login} onChange={e=>setLogin(e.target.value)} />
        </label>
        <label>
          Password
          <input type="password" value={password} onChange={e=>setPassword(e.target.value)} />
        </label>
        <button className="btn" disabled={loading} type="submit">{loading? 'Signing...' : 'Sign in'}</button>
      </form>
    </div>
  )
}
