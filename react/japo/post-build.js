const replace = require('replace-in-file');
const options = {
    files: 'build/.htaccess',
    from: /\$\{PUBLIC_URL\}/g,
    to: process.env.PUBLIC_URL === undefined ? '' : process.env.PUBLIC_URL,
};
try {
    console.log(process.cwd());
    const changes = replace.sync(options);
    console.log('Modified files:', changes.join(', '));
}
catch (error) {
    console.error('Error occurred:', error);
}