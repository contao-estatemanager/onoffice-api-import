export class OnOfficeImport {
    constructor() {
        this.modules = {}

        for(const m of document.querySelectorAll('[data-module]'))
        {
            if(!m.classList.contains('disabled'))
            {
                const moduleName = m.dataset.module
                const moduleBtn = m.querySelector('button')
                const moduleProg = m.querySelector('.progress')

                // Register module
                this.modules[moduleName] = {
                    dom: m,
                    module: moduleName,
                    button: moduleBtn,
                    progress: moduleProg
                }

                // Register event
                moduleBtn.addEventListener('click', (e) => this.start(moduleName))
            }
        }
    }

    start(moduleName) {
        const module = this.modules[moduleName];

        // Disable import button
        module.button.disabled = true

        // Display progression
        module.progress.style.display = 'block'

        console.log('Import', this.modules[moduleName])
    }
}
