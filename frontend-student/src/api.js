import axios from 'axios'

const api = axios.create({
  baseURL: '',
  withCredentials: true
})

export async function searchCourses(name = '') {
  const res = await api.post('/api/Course/search/', { name })
  return res.data
}

export async function getCourse(courseId) {
  const res = await api.get('/api/Course/get/', { params: { courseId } })
  return res.data
}

export async function searchBlocks(courseId, name = '') {
  const res = await api.post('/api/Block/search/', { courseId, name })
  return res.data
}

export async function getBlock(blockId) {
  const res = await api.get('/api/Block/get/', { params: { blockId } })
  return res.data
}

export async function getAnswersByBlock(blockId) {
  const res = await api.get('/api/Answer/getByBlock/', { params: { blockId } })
  return res.data
}

export async function getAnswer(answerId) {
  const res = await api.get('/api/Answer/get/', { params: { answerId } })
  return res.data
}

export async function login(credentials) {
  const res = await api.post('/api/Auth/login/', credentials)
  return res.data
}

export async function logout() {
  const res = await api.post('/api/Auth/logout/')
  return res.data
}

export async function getUser() {
  const res = await api.get('/api/Auth/getUser/')
  return res.data
}

export async function createAnswer({ blockId, studentId, file, text }) {
  const fd = new FormData()
  fd.append('blockId', blockId)
  if (studentId) fd.append('studentId', studentId)
  if (text) fd.append('text', text)
  if (file) fd.append('file', file)

  const res = await api.post('/api/Answer/create/', fd, {
    headers: { 'Content-Type': 'multipart/form-data' }
  })
  return res.data
}

export default api
