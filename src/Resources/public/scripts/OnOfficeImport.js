import ImportModule from "./controller/ImportModule";

export class OnOfficeImport {
    constructor(options) {
        this.modules = {}

        for(const m of document.querySelectorAll('[data-module]'))
        {
            if(!m.classList.contains('disabled'))
            {
                const moduleName = m.dataset.module
                const module = new ImportModule(moduleName, m);

                // Register events
                module.elements.buttons.import.addEventListener('click', (event) => {
                    event.preventDefault()

                    if(confirm(options.texts.confirmMessage)) {
                        if(module.hasSettings() && module.elements.form.checkValidity()) {
                            module.start();
                        } else {
                            module.elements.blocks.settings.classList.add('open')
                            module.elements.buttons.settings.classList.add('open')
                            module.elements.form.reportValidity()
                        }
                    }
                })

                if(module.hasSettings()) {
                    module.elements.buttons.settings.addEventListener('click', (event) => {
                        event.preventDefault()
                        module.elements.blocks.settings.classList.toggle('open')
                        module.elements.buttons.settings.classList.toggle('open')
                    })
                }

                // Add module to collection
                this.modules[moduleName] = module;
            }
        }
    }
}
