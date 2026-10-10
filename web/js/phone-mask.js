/*!
 * Маска для поля телефона в форме входа (без внешних зависимостей).
 *
 * Формат отображения: +7 (999) 999-99-99
 * Формат хранения/отправки: +79999999999 (совпадает с полем phone в БД).
 */
(() => {
    'use strict'

    const DIGITS_MAX = 11 // 7 + 10 значащих цифр

    const format = (digits) => {
        // digits — только цифры, начинаются с 7
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
        // Приводим первую цифру к 7 (8 -> 7, 9 -> 7)
        if (digits[0] === '8' || digits[0] === '9') {
            digits = '7' + digits.slice(1)
        }
        // Если ввели без ведущей 7/8 и это не 7 — считаем, что пропущена 7
        if (digits[0] !== '7') {
            digits = '7' + digits
        }
        return digits
    }

    const toStorageValue = (value) => {
        const digits = normalizeDigits(value)
        return digits === '' ? '' : '+' + digits
    }

    const init = () => {
        const input = document.querySelector('#login-form input[name="LoginForm[username]"]')
        if (!input) return

        input.setAttribute('inputmode', 'tel')
        input.setAttribute('autocomplete', 'tel')

        const apply = () => {
            const digits = normalizeDigits(input.value)
            input.value = digits === '' ? '' : format(digits)
        }

        // Пользователь вводит — форматируем на лету
        input.addEventListener('input', apply)

        // При фокусе, если поле пустое — подставляем +7 (
        input.addEventListener('focus', () => {
            if (input.value.trim() === '') {
                input.value = '+7 ('
            }
        })

        // Обрабатываем backspace, чтобы маска не «залипала»
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace') {
                const pos = input.selectionStart
                // Если курсор сразу после спецсимвола маски — сдвигаем его назад
                if (pos > 0 && /[ ()\-]/.test(input.value[pos - 1])) {
                    e.preventDefault()
                    input.setSelectionRange(pos - 1, pos - 1)
                    input.dispatchEvent(new Event('input', { bubbles: true }))
                }
            }
        })

        // Перед отправкой — приводим к формату хранения +79999999999
        const form = input.form
        if (form) {
            form.addEventListener('submit', () => {
                input.value = toStorageValue(input.value)
            })
            // restore: если браузер вернулся назад после серверной валидации
            form.addEventListener('reset', () => {
                input.value = ''
            })
        }

        // Если значение уже подставлено сервером (repopulate) — отформатируем
        apply()
    }

    document.addEventListener('DOMContentLoaded', init)
})()
