export default {
    getValue(key) {
        let data = localStorage.getItem('userInfo');
        if (!data) data = '{}';
        data = JSON.parse(data);
        return data[key];
    },
    setValue(key, value) {
        let data = localStorage.getItem('userInfo');
        if (!data) data = '{}';
        data = JSON.parse(data);
        data[key] = value;
        localStorage.setItem('userInfo', JSON.stringify(data));
    }
}