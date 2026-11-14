const fs = require('node:fs');
const path = require('node:path');

const migrated = new Set(['ERP1000.php', 'ERP1060.php']);
const pages = fs.readdirSync('legacy').filter(file => file.endsWith('.php')).sort();
const rows = pages.map(file => {
    const source = fs.readFileSync(path.join('legacy', file), 'utf8');
    const active = source.replace(/\/\*[\s\S]*?\*\//g, '').replace(/^\s*\/\/.*$/gm, '');
    const permission = active.match(/fxValidarPermiso\(\s*['"]([^'"]+)['"]/)?.[1] || 'Not declared';
    const classes = [...new Set([...source.matchAll(/require_once\s*['"]Clases\/([^'"]+)['"]/g)].map(match => match[1]))];
    const queries = classes.reduce((total, file) => {
        const filename = path.join('legacy', 'Clases', file);
        return total + (fs.existsSync(filename) ? [...fs.readFileSync(filename, 'utf8').matchAll(/->omExec\(/g)].length : 0);
    }, 0);
    return `| ${file} | ${migrated.has(file) ? 'Symfony controller and DBAL repositories, pending runtime validation' : 'Legacy'} | ${permission} | ${classes.join(', ') || 'None'} | ${queries} |`;
});
const document = `# Business module migration inventory

${pages.length} root PHP scripts are present. ${migrated.size} maintenance routes have new
controllers, services and DBAL repositories. The remaining scripts still execute
legacy business code. Authentication and shared menu rendering remain legacy.

This inventory counts SQL calls in directly included classes. A class can serve
several screens, so counts overlap and are not migration estimates. Permission
names are read from active calls in the entry script, not from database grants.
"Not declared" requires an access review before migrating that route.

Regenerate with node bin/migration-inventory.cjs.

| Script | State | Existing permission | Direct business classes | SQL calls in classes |
| --- | --- | --- | --- | ---: |
${rows.join('\n')}
`;
fs.writeFileSync('docs/MODULE-MIGRATION.md', document);
console.log(`Inventory written: ${pages.length} scripts, ${migrated.size} routes implemented, pending PHP and database validation.`);
