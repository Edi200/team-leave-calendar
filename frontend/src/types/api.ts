export interface ApiCollectionResponse<T> {
  data: T[]
}

export interface ApiResourceResponse<T> {
  data: T
}

export interface ValidationErrorResponse {
  message: string
  errors: Record<string, string[]>
}
