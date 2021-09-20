export class OnOfficeImport {
    constructor(options) {
        this.modules = {}

        for(const m of document.querySelectorAll('[data-module]'))
        {
            if(!m.classList.contains('disabled'))
            {
                const moduleName = m.dataset.module

                const buttonSettings = m.querySelector('button[type="button"]')
                const containerSettings = m.querySelector('.settings')
                const buttonImport = m.querySelector('button[type="submit"]')
                const formSettings = m.querySelector('form')

                // Register module
                this.modules[moduleName] = {
                    name: moduleName,
                    hasSettings: !!containerSettings,
                    data: null,
                    elements: {
                        module: m,
                        buttonImport: buttonImport,
                        formSettings: formSettings,
                        buttonSettings: buttonSettings,
                        containerSettings: containerSettings,
                        progressBar: m.querySelector('.progress-value'),
                        containerProgress: m.querySelector('.progress'),
                        containerCounter: m.querySelector('.progress .current'),
                        containerNumber: m.querySelector('.progress .count'),
                        containerInfo: m.querySelector('.progress .info')
                    }
                }

                // Register event
                buttonImport.addEventListener('click', (event) => {
                    event.preventDefault()

                    if(confirm(options.texts.confirmMessage)) {
                        if(formSettings.checkValidity()) {
                            this.start(moduleName)
                        }else{
                            containerSettings.classList.add('open');
                            buttonSettings.classList.add('open')

                            formSettings.reportValidity()
                        }
                    }
                })

                if(buttonSettings) {
                    buttonSettings.addEventListener('click', (event) => {
                        event.preventDefault()

                        // Toggle settings container
                        containerSettings.classList.toggle('open');
                        buttonSettings.classList.toggle('open')
                    })
                }
            }
        }
    }

    start(moduleName) {
        const module = this.modules[moduleName]

        // Disable import button
        module.elements.buttonImport.disabled = true

        // Display progression
        module.elements.containerProgress.style.display = 'block'

        if(module.hasSettings) {
            // Set import data
            this.modules[moduleName].data = new FormData(module.elements.formSettings)

            // Hide settings
            module.elements.buttonSettings.classList.remove('open')
            module.elements.containerSettings.classList.remove('open')
        }

        // Fetch data
        this.fetch(moduleName)
            .then(r => this.onFinish(moduleName))
            .catch(e => this.onError(moduleName))
    }

    async fetch(moduleName) {
        const module = this.modules[moduleName]
        const source = await fetch('/onoffice/fetch/' + moduleName, {
            method: 'POST',
            body: module.data
        });

        const data = await source.json()

        this.setStatus(moduleName, data)

        if(data.task) {
            await this.import(moduleName, data.task)
        }
    }

    async import(moduleName, task) {
        // Set new data
        console.log('Import:', task)

        // Start import
        const source = await fetch(task.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(task)
        });

        const data = await source.json()

        this.setStatus(moduleName, data)

        if(data.task) {
            await this.import(data.task)
        }

        console.log('Import', data)
    }

    setStatus(moduleName, data) {
        if(data.message) {
            this.modules[moduleName].elements.containerInfo.innerText = data.message
        }

        if(data.meta?.cntabsolute) {
            this.modules[moduleName].countAbsolute = data.meta.cntabsolute;
            this.modules[moduleName].elements.containerNumber.innerText = data.meta.cntabsolute
        }

        if(data.count) {
            this.modules[moduleName].elements.containerCounter.innerText = data.count
        }

        if(this.modules[moduleName].countAbsolute && data.count) {
            this.setProgress(moduleName, data.count / this.modules[moduleName].countAbsolute * 100)
        }
    }

    setProgress(moduleName, percentage) {
        this.modules[moduleName].elements.progressBar.style.width = percentage + "%"
    }

    onFinish(moduleName) {
        console.log('onFinish')

        this.setProgress(moduleName, 100)

        this.modules[moduleName].elements.containerInfo.classList.add('success')
    }

    onError(moduleName) {
        console.error(moduleName)
    }
}
