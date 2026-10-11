/*!
 * Маска телефона +7 (999) 999-99-99 → хранение +79999999999
 * Поля: [data-phone-mask] или #login-form input[name="LoginForm[username]"]
 */
(() => {
    'use strict'

    const DIGITS_MAX = 11

    const format = (digits) => {
        const d = digits.slice(0, DIGITS_MAX)
        const rest = d.slice(1)

        let out = '+7'
        if (rest.length > 0) out += ' (' + rest.slice(0, 3)
        if (rest.length >= 3) out += ')'
        if (rest.length > 3) out += ' ' + rest.slice(3, 6)
        if (rest.length > 6) out += '-' + rest.slice(6, 8)
        if (rest.length > 8) out += '-' + rest.slice(8, 10)

        return out
    }

    const normalizeDigits = (value) => {
        let digits = value.replace(/\D/g, '')
        if (digits.length === 0) return ''
        if (digits[0] === '8' || digits[0] === '9') {
            digits = '7' + digits.slice(1)
        }
        if (digits[0] !== '7') {
            digits = '7' + digits
        }
        return digits
    }

    const toStorageValue = (value) => {
        const digits = normalizeDigits(value)
        return digits === '' ? '' : '+' + digits
    }

    const bindPhoneMask = (input) => {
        if (!input || input.dataset.phoneMaskBound === '1') {
            return
        }
        input.dataset.phoneMaskBound = '1'

        input.setAttribute('inputmode', 'tel')
        if (!input.getAttribute('autocomplete')) {
            input.setAttribute('autocomplete', 'tel')
        }

        const apply = () => {
            const digits = normalizeDigits(input.value)
            input.value = digits === '' ? '' : format(digits)
        }

        input.addEventListener('input', apply)

        input.addEventListener('focus', () => {
            if (input.value.trim() === '') {
                input.value = '+7 ('
            }
        })

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace') {
                const pos = input.selectionStart
                if (pos > 0 && /[ ()\-]/.test(input.value[pos - 1])) {
                    e.preventDefault()
                    input.setSelectionRange(pos - 1, pos - 1)
                    input.dispatchEvent(new Event('input', { bubbles: true }))
                }
            }
        })

        const form = input.form
        if (form) {
            form.addEventListener('submit', () => {
                input.value = toStorageValue(input.value)
            })
        }

        apply()
    }

    const init = () => {
        document.querySelectorAll('[data-phone-mask]').forEach(bindPhoneMask)

        const loginInput = document.querySelector('#login-form input[name="LoginForm[username]"]')
        if (loginInput) {
            bindPhoneMask(loginInput)
        }
    }

    document.addEventListener('DOMContentLoaded', init)
})()
