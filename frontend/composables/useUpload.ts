export const useUpload = () => {
    const uploadImage = async (file: File): Promise<string> => {
        const formData = new FormData()
        formData.append('image', file)

        const { data, error } = await useFetch<any>('/api/upload', {
            method: 'POST',
            body: formData
        })

        if (error.value) {
            throw new Error(error.value.message || 'Upload failed')
        }

        return data.value.url
    }

    return {
        uploadImage
    }
}
