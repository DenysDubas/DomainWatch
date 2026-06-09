export function unwrapResource<T>(body: T | { data: T }): T {
  if (body !== null && typeof body === 'object' && 'data' in body) {
    return (body as { data: T }).data
  }
  return body as T
}

export function unwrapCollection<T>(body: { data?: T[] } | T[]): T[] {
  if (Array.isArray(body)) return body
  return body.data ?? []
}
