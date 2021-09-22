export default class ImportModule {

    constructor(name, element) {
        this.name = name
        this.element = element
        this.elements = {
            form:         this.element.querySelector('form'),
            buttons: {
                import:   this.element.querySelector('button[type="submit"]'),
                settings: this.element.querySelector('button[type="button"]')
            },
            blocks: {
                settings: this.element.querySelector('.settings'),
                progress: this.element.querySelector('.progress'),
            },
            progress: {
                current:  this.element.querySelector('.progress .current'),
                number:   this.element.querySelector('.progress .count'),
                info:     this.element.querySelector('.progress .info'),
                bar:      this.element.querySelector('.progress-value')
            }
        }

        this.data = null
    }

    hasSettings() {
        return !!this.elements.form
    }

    start() {
        // Disable import button
        this.elements.buttons.import.disabled = true

        // Display progression
        this.elements.blocks.progress.style.display = 'block'

        // Reset progress
        this.elements.progress.info.classList.remove('success', 'error')
        this.setStatus({
            message: 'Retrieve data...',
            count: 0,
            countAbsolute: 0,
            simulateProgress: 0
        })

        if(this.hasSettings()) {
            // Set import data
            this.data = new FormData(this.elements.form)

            // Hide settings
            this.elements.buttons.settings.classList.remove('open')
            this.elements.blocks.settings.classList.remove('open')
        }

        // Fetch data
        this.fetch()
            .then(r => this.onFinish())
            .catch(e => this.onError(e))
    }

    async fetch() {
        const source = await fetch('/onoffice/fetch/' + this.name, {
            method: 'POST',
            body: this.data
        });

        const data = await source.json()

        this.setStatus(data)

        if(data.task && this.countAbsolute) {
            await this.import(data.task)
        }
    }

    async import(task) {
        const source = await fetch(task.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(task)
        });

        const data = await source.json()

        this.setStatus(data)

        if(data.task && this.countAbsolute) {
            await this.import(data.task)
        }
    }

    setStatus(data) {
        if(data.message) {
            this.elements.progress.info.innerText = data.message
        }

        if(data.countAbsolute || data.countAbsolute === 0) {
            this.countAbsolute = data.countAbsolute
            this.elements.progress.number.innerText = data.countAbsolute
        }

        if(data.count || data.count === 0) {
            this.elements.progress.current.innerText = data.count
        }

        if(this.countAbsolute && data.count) {
            this.setProgress(data.count / this.countAbsolute * 100)
        }

        if(data.simulateProgress || data.simulateProgress === 0) {
            this.setProgress(data.simulateProgress)
        }
    }

    setProgress(percentage) {
        this.elements.progress.bar.style.width = percentage + "%"
    }

    onFinish() {
        this.setProgress(100)
        this.elements.progress.info.classList.add('success')

        // Enable import button
        this.elements.buttons.import.disabled = false
    }

    onError(e) {
        this.elements.progress.info.innerText = 'Import failed';
        this.elements.progress.info.classList.add('error')

        console.error(this.name, e)
    }
}
