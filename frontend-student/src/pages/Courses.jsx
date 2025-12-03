import React, { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { searchCourses } from '../api'
import { normalizeToArray, getSafeId } from '../utils/apiHelpers'

export default function Courses() {
  const [q, setQ] = useState('')
  const [courses, setCourses] = useState([])
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    load()
  }, [])

  async function load() {
    setLoading(true)
    try {
      const data = await searchCourses()
      setCourses(normalizeToArray(data))
    } finally {
      setLoading(false)
    }
  }

  async function onSearch(e) {
    e.preventDefault()
    setLoading(true)
    try {
      const data = await searchCourses(q)
      setCourses(normalizeToArray(data))
    } finally {
      setLoading(false)
    }
  }

  return (
    <div>
      <h2>Курсы</h2>
      <form onSubmit={onSearch} className="search">
        <input value={q} onChange={e => setQ(e.target.value)} placeholder="Поиск курсов..." />
        <button className="btn" type="submit">Искать</button>
        <button className="btn secondary" type="button" onClick={load}>Сбросить</button>
      </form>

      {loading && <div>Загрузка...</div>}

      <div className="list">
        {courses.length === 0 && !loading && <div>Курсы не найдены</div>}
        {courses.map(c => {
          const cId = getSafeId(c)
          return (
            <Link key={cId} to={`/courses/${cId}/blocks`} className="card">
              <h3>{c.name}</h3>
            </Link>
          )
        })}
      </div>
    </div>
  )
}
