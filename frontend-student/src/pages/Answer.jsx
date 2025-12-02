import React, { useEffect, useState } from 'react'
import { useParams } from 'react-router-dom'
import { getBlock, getCourse, getAnswersByBlock, createAnswer } from '../api'
import { useAuth } from '../auth/AuthProvider'
import { unwrapResult, isAnswerExists, fmt, getFileHref, logAxiosError } from '../utils/apiHelpers'

export default function Answer() {
  const { courseId, blockId, answerId } = useParams()
  const [block, setBlock] = useState(null)
  const [course, setCourse] = useState(null)
  const [answer, setAnswer] = useState(null)
  const [answerExists, setAnswerExists] = useState(false)
  const [loading, setLoading] = useState(false)

  const [file, setFile] = useState(null)
  const [text, setText] = useState('')
  const { user } = useAuth()
  const [studentId, setStudentId] = useState(user?.id || user?.studentId || '')
  const [fileHref, setFileHref] = useState(null)

  useEffect(() => {
    load()
  }, [blockId])

  async function load() {
    setLoading(true)
    try {
      let b = null
      try {
        b = await getBlock(blockId)
        b = unwrapResult(b)
      } catch (err) {
        logAxiosError(err, 'getBlock error')
      }
      setBlock(b)
      setFileHref(b ? getFileHref(b.file) : null)

      let c = null
      try {
        c = await getCourse(courseId)
        c = unwrapResult(c)
      } catch (err) {
        logAxiosError(err, 'getCourse error')
      }
      setCourse(c)

      let ans = null
      let exists = false
      try {
        ans = await getAnswersByBlock(blockId)
        ans = unwrapResult(ans)
        exists = isAnswerExists(ans)
      } catch (err) {
        logAxiosError(err, 'getAnswersByBlock error')
      }
      setAnswer(ans)
      setAnswerExists(exists)
    } catch (err) {
      logAxiosError(err, 'load error')
    } finally {
      setLoading(false)
    }
  }

  async function onSubmit(e) {
    e.preventDefault()
    try {
      const res = await createAnswer({ blockId, studentId: studentId || (user?.id || user?.studentId), file, text })
      alert('Ответ отправлен')
      setFile(null)
      setText('')
      load()
    } catch (err) {
      logAxiosError(err, 'createAnswer error')
      const msg = err?.response?.data?.errorMessage || err?.response?.data?.message || err?.message || 'Ошибка отправки'
      alert(msg)
    }
  }

  return (
    <div className="no-card-anim">
      <h2>{block ? block.name : 'Block'}</h2>
      {loading && <div>Загрузка...</div>}

      {block && (
        <div className="card">
          <div className="block-desc">{block.description || '-'}</div>
          <div><strong>Тип:</strong> {fmt(block.type)}</div>
          <div><strong>Начало:</strong> {fmt(block.dataStart)}</div>
          <div><strong>Конец:</strong> {fmt(block.dataEnd)}</div>
          <div><strong>Максимальный балл:</strong> {block.maxScore || block.maxscore || '-'}</div>
          {fileHref && (
            <div className="file-section">
              <strong>Файл:</strong>{' '}
              <a className="btn secondary" href={fileHref} target="_blank" rel="noopener noreferrer" download>
                Скачать
              </a>
            </div>
          )}
        </div>
      )}

      {!answerExists && (
        <section>
          <h3>Отправить ответ</h3>
          <form onSubmit={onSubmit} className="answer-form">
            <label>Файл</label>
            <input type="file" onChange={e => setFile(e.target.files[0])} />
            <button className="btn" type="submit">Отправить</button>
          </form>
        </section>
      )}

      {answerExists && (
        <section className="answered-section">
          <div className="card">
            <h3>✓ Ответ зафиксирован</h3>
            <p>Вы ответили на это задание. Изменить ответ нельзя.</p>
          </div>
        </section>
      )}
    </div>
  )
}
