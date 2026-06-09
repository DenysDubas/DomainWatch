export function formatCheckError(message: string | null): string {
  if (!message) return '—'

  const curlMatch = message.match(/cURL error \d+:\s*(.+?)\s*(?:\(see https:|for https?:\/\/)/s)
  if (curlMatch?.[1]) return curlMatch[1].trim()

  const hostMatch = message.match(/Could not resolve host:\s*(\S+)/)
  if (hostMatch) return `Could not resolve host: ${hostMatch[1]}`

  if (message.includes('Connection timed out') || message.includes('Operation timed out')) {
    return 'Connection timed out'
  }

  if (message.includes('SSL') || message.includes('certificate')) {
    return 'SSL certificate error'
  }

  return message.length > 80 ? `${message.slice(0, 77)}...` : message
}
