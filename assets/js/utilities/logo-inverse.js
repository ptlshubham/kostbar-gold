/* logo-inverse.js | https://www.indonez.com | Indonez | MIT License */
class LogoInverse {
    constructor() {
        this.element = document.querySelector('[data-logo-inverse]') !== null ?document.querySelector('[data-logo-inverse]') : false
    }

    init() {
        if (this.element) {
            JSON.parse(this.extractAttribute().stickyOnly) && this.stickyInverse()
            this.replacePath(this.element)
        }
    }

    replacePath(element) {
        const rawPath = element.querySelector('img').getAttribute('src').split('/')
        rawPath[rawPath.length - 1] = this.extractAttribute().filename

        element.querySelector('img').setAttribute('src', rawPath.join('/'))
    }

    extractAttribute() {
        const attrValue = this.element.getAttribute('data-logo-inverse').split('; ')
        const dataAttr = {}

        attrValue.forEach(attr => {
            const sanitize = attr.replace(/(;)/g, '')
            Object.assign(dataAttr, {[sanitize.match(/(.*):/)[1].replace(/[-_\s.]+(.)?/g, (_, c) => c ? c.toUpperCase() : '')]: sanitize.match(/: (.*)/)[1]})
        })

        if (!dataAttr.hasOwnProperty('stickyOnly')) {
            Object.assign(dataAttr, {stickyOnly: 'false'})
        }

        return dataAttr
    }

    stickyInverse() {
        const originalLogo = document.querySelector('[data-logo-inverse] img').getAttribute('src')
        const observer = new MutationObserver(mutations => {
            if (mutations[0].target.children[0].classList.contains('uk-sticky-fixed')) {
                this.replacePath(this.element)
            } else {
                document.querySelector('[data-logo-inverse] img').setAttribute('src', originalLogo)
            }
        })
        observer.observe(document.querySelector('header'), {childList: true, subtree: true})
    }
}

new LogoInverse().init()