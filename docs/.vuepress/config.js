module.exports = {
    title: 'Organizations',
    description: 'Simple parent + child management',
    base: '/',
    //theme: 'flipbox',
    themeConfig: {
        docsRepo: 'flipboxfactory/organizations',
        docsDir: 'docs',
        docsBranch: 'master',
        editLinks: true,
        search: true,
        searchMaxSuggestions: 10,
        codeLanguages: {
            twig: 'Twig',
            php: 'PHP',
            json: 'JSON',
            // any other languages you want to include in code toggles...
        },
        nav: [
            {text: 'Details', link: 'https://flipboxfactory.com/craft-cms-plugins/organizations'},
            {text: 'Changelog', link: 'https://github.com/flipboxfactory/organizations/blob/master/CHANGELOG.md'},
            {text: 'Repo', link: 'https://github.com/flipboxfactory/organizations'}
        ],
        sidebar: {
            '/': [
                {
                    title: 'Getting Started',
                    collapsable: false,
                    children: [
                        ['/', 'Introduction'],
                        ['/requirements', 'Requirements'],
                        ['/installation', 'Installation / Upgrading'],
                        ['/support', 'Support'],
                    ]
                },
                {
                    title: 'Configure',
                    collapsable: false,
                    children: [
                        ['/configure/', 'Overview'],
                        ['/configure/organization-types', 'Organization Types'],
                        ['/configure/user-types', 'User Types']
                    ]
                },
                {
                    title: 'Templating',
                    collapsable: false,
                    children: [
                        ['/templating/', 'Overview']
                    ]
                },
                {
                    title: 'Services',
                    collapsable: false,
                    children: [
                        ['/services/elements', 'Organization Elements'],
                        ['/services/organization-types', 'Organization Types'],
                        ['/services/users', 'User Elements'],
                        ['/services/user-types', 'User Types']
                    ]
                },
                {
                    title: 'Objects',
                    collapsable: false,
                    children: [
                        ['/objects/organization', 'Organization'],
                        ['/objects/organization-type', 'Organization Type'],
                        ['/objects/organization-type-site-settings', 'Organization Type Site Settings'],
                        ['/objects/user', 'User'],
                        ['/objects/user-type', 'User Type'],
                        ['/objects/settings', 'Settings'],
                    ]
                },
                {
                    title: 'Queries',
                    collapsable: false,
                    children: [
                        ['/queries/organization', 'Organization Query'],
                        ['/queries/organization-type', 'Organization Type Query'],
                        ['/queries/user', 'User Query'],
                        ['/queries/user-type', 'User Type Query']
                    ]
                }
            ]
        }
    },
    markdown: {
        anchor: { level: [2, 3] },
        toc: { includeLevel: [3] },
        config(md) {
            let markup = require('./markup') // TODO Change after using theme
            md.use(markup)
        }
    }
}