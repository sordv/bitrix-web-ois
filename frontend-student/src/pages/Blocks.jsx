import React, { useEffect, useState } from 'react'
import { useParams, Link } from 'react-router-dom'
import { searchBlocks, getCourse } from '../api'
import { unwrapResult, normalizeToArray, getSafeId, logAxiosError } from '../utils/apiHelpers'

export default function Blocks() {
  const { courseId } = useParams()
  const [q, setQ] = useState('')
  const [blocks, setBlocks] = useState([])
  const [course, setCourse] = useState(null)
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    loadCourse()
    loadBlocks()
  }, [courseId])

  async function loadCourse() {
    try {
      let c = await getCourse(courseId)
      c = unwrapResult(c)
      setCourse(c)
    } catch (e) {
      logAxiosError(e, 'loadCourse error')
    }
  }

  async function loadBlocks() {
    setLoading(true)
    try {
      const data = await searchBlocks(courseId, '')
      setBlocks(normalizeToArray(data))
    } finally {
      setLoading(false)
    }
  }

  async function onSearch(e) {
    e.preventDefault()
    setLoading(true)
    try {
      const data = await searchBlocks(courseId, q)
      setBlocks(normalizeToArray(data))
    } finally {
      setLoading(false)
    }
  }

  return (
    <div>
      <h2>{course ? course.name : 'Курс'}</h2>

      <form onSubmit={onSearch} className="search">
        <input value={q} onChange={e => setQ(e.target.value)} placeholder="Поиск блоков..." />
        <button className="btn" type="submit">Искать</button>
        <button className="btn secondary" type="button" onClick={loadBlocks}>Сбросить</button>
      </form>

      {loading && <div>Загрузка...</div>}

      <div className="list">
        {blocks.length === 0 && !loading && <div>Блоки не найдены</div>}
        {blocks.map(b => {
          const bId = getSafeId(b)
          return (
            <Link key={bId} to={`/courses/${courseId}/blocks/${bId}/answer/1`} className="card">
              <h3>{b.name}</h3>
              <p>{b.description}</p>
            </Link>
          )
        })}
      </div>
    </div>
  )
}
