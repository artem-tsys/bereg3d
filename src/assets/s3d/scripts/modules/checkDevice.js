const isDevice = (type = 'mobile') => {
    if (type === 'ios') {
        return /iPhone|iPad|iPod/.test(navigator.userAgent)
    }
    return ((navigator.maxTouchPoints && navigator.maxTouchPoints > 2) || /Android|webOS|BlackBerry|BB|PlayBook|IEMobile|Windows Phone|Kindle|Silk|Opera Mini|iPhone|iPad|iPod/.test(navigator.userAgent))
}
export default isDevice;