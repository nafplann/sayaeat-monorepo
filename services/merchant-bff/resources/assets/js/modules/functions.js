const Functions = {
    async fetchPictureByRef(ref) {
        const result = await Utils.ajax(Utils.baseUrl(`/manage/functions/fetch-picture-by-ref`), 'GET', 'json', { ref })
            .fail(xhr => {
            })
            .then(response => response);

        return result;
    }
};

export default Functions;
