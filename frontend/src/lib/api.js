const apiBaseUrl = '/api'

export const apiUrl = (path) => {
  const normalizedPath = path.startsWith('/') ? path : `/${path}`

  return `${apiBaseUrl}${normalizedPath}`
}
