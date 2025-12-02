export function unwrapResult(data) {
  if (!data) return data
  if (data.result && typeof data.result === 'object') {
    return data.result
  }
  return data
}

export function normalizeToArray(data) {
  if (Array.isArray(data)) return data
  if (!data) return []

  const unwrapped = unwrapResult(data)
  if (Array.isArray(unwrapped)) return unwrapped

  if (Array.isArray(unwrapped?.courses)) return unwrapped.courses
  if (Array.isArray(unwrapped?.items)) return unwrapped.items
  if (Array.isArray(unwrapped?.blocks)) return unwrapped.blocks
  if (Array.isArray(unwrapped?.result)) return unwrapped.result

  return []
}

export function isAnswerExists(ans) {
  if (!ans) return false
  return (
    ans.code !== null ||
    ans.blockId !== null ||
    ans.studentId !== null ||
    ans.file !== null ||
    ans.review !== null ||
    ans.score !== null
  )
}

export function fmt(v) {
  if (v === null || v === undefined || v === '') return '-'
  if (typeof v === 'object') {
    try {
      return JSON.stringify(v)
    } catch (e) {
      return String(v)
    }
  }
  return v
}

export function getFileHref(file) {
  if (!file) return null
  if (file === '0' || file === 'null') return null

  if (/^https?:\/\//i.test(file)) return file
  try {
    return new URL(file, window.location.origin).href
  } catch (e) {
    return null
  }
}

export function logAxiosError(err, label = 'AxiosError') {
  if (!err) return
  try {
    if (err.response) {
      console.error(label, 'status:', err.response.status, 'data:', err.response.data)
    } else {
      console.error(label, err.message || err)
    }
  } catch (e) {
    console.error('Error while logging axios error', e)
  }
}

export function getSafeId(obj) {
  if (!obj) return null
  return obj.id || obj.code || obj.blockId || obj.courseId || obj.courseID || obj.answerId || null
}
