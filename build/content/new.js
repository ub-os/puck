import fs from 'fs';

function toSnakeCase(str) {
    return str.split(/\.?(?=[A-Z])/).join('_').toLowerCase();
}
function build() {
    const args = process.argv.slice(2)
    if (!args[0]) {
        console.log('Usage: npm run build:content:new <ContentName> [options]');
        return false;
    }
    const options = {
        modelName: args[0]

    }
    const extensionName = 'puck';
    const paths = {
        model: 'puck/Classes/Domain/Model/Content',
        tca: 'puck/Configuration/TCA/Content',
        template: 'puck/Resources/Private/Fluid/Content',
    };
    const files = {
        model: `${paths.model}/${options.modelName}.php`,
        tca: `${paths.tca}/types.php`,
        template: `${paths.template}/${options.modelName}.html`,
    };
    const contents = {
        model: fs.readFileSync(`build/content/new/Model.php`, 'utf8')
                .replace('class Model', `class ${options.modelName}`),
        tca: fs.readFileSync(files.tca, 'utf8')
                .replace(
                    'return $types;',
                    fs.readFileSync(`build/content/new/tca.php`, 'utf8')
                        .replace(
                            'puck_model',
                            `puck_${toSnakeCase(options.modelName)}`
                        )+'' +
                    'return $types;'
                ),
        template: `<f:debug>{_all}</f:debug>`
    };


    if (args[1]) {
        for (let o of args[2].split('+')) {
            let keyValuePair = o.split('=');
            options[keyValuePair[0]] = keyValuePair[1];
        }
    }

    fs.writeFile(files.model, contents.model, function(){});
    fs.writeFile(files.tca, contents.tca, function(){});
    fs.writeFile(files.template, contents.template, function(){});
    console.log(
        `Content "${options.modelName}" created.
        Created files:
            - ${files.model}
            - ${files.template}
        Modified files:
            - ${files.tca}    
   `);
}
build();