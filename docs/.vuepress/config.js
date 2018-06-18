module.exports = {
    title: 'Organizations',
    description: 'Simple parent + child management',
    base: '/',
    themeConfig: {
        docsRepo: 'flipboxfactory/organizations',
        docsDir: 'docs',
        docsBranch: 'master',
        editLinks: true,
        search: true,
        searchMaxSuggestions: 10,
        nav: [
            {text: 'Details', link: 'https://github.com/craftcms/docs/'},
            {text: 'Changelog', link: 'https://github.com/craftcms/docs/'},
            {text: 'Documentation', link: '/'}
        ],
        sidebar: {
            '/': [
                {
                    title: 'Getting Started',
                    collapsable: false,
                    children: [
                        ['/', 'Introduction'],
                        'requirements',
                        ['installation', 'Installation / Upgrading'],
                        'support'
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
                        ['/objects/settings', 'Settings'],
                        ['/objects/user-type', 'User Type'],
                        ['/objects/user', 'User']
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
        },
        codeLanguages: {
            twig: 'Twig',
            php: 'PHP'
        }
    },
    markdown: {
        anchor: {
            level: [2, 3, 4]
        },
        toc: {
            includeLevel: [3]
        }
    }
}