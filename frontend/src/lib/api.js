const apiBaseUrl = (import.meta.env.VITE_API_URL || '/api').replace(/\/$/, '')

export const apiUrl = (path) => {
  const normalizedPath = path.startsWith('/') ? path : `/${path}`

  return `${apiBaseUrl}${normalizedPath}`
}
