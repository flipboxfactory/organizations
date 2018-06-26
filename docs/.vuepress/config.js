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
            {text: 'Documentation', link: 'https://SET_URL'},
            {text: 'Changelog', link: 'https://github.com/flipboxfactory/organizations/blob/master/CHANGELOG.md'},
            {text: 'Repo', link: 'https://github.com/flipboxfactory/organizations'}
        ],
        sidebar: {
            '/': [
                {
                    title: 'Getting Started',
                    collapsable: true,
                    children: [
                        ['/', 'Introduction'],
                        ['/requirements', 'Requirements'],
                        ['/installation', 'Installation / Upgrading'],
                        ['/support', 'Support'],
                    ]
                },
                {
                    title: 'Configure',
                    collapsable: true,
                    children: [
                        ['/configure/', 'Overview'],
                        ['/configure/organization-types', 'Organization Types'],
                        ['/configure/user-types', 'User Types']
                    ]
                },
                {
                    title: 'Templating',
                    collapsable: true,
                    children: [
                        ['/templating/', 'Overview']
                    ]
                },
                {
                    title: 'Services',
                    collapsable: true,
                    children: [
                        ['/services/elements', 'Organization Elements'],
                        ['/services/organization-types', 'Organization Types'],
                        ['/services/users', 'User Elements'],
                        ['/services/user-types', 'User Types']
                    ]
                },
                {
                    title: 'Objects',
                    collapsable: true,
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
                    collapsable: true,
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