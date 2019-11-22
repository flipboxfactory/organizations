(window.webpackJsonp=window.webpackJsonp||[]).push([[8],{206:function(t,a,n){"use strict";n.r(a);var e=n(0),s=Object(e.a)({},(function(){var t=this,a=t.$createElement,n=t._self._c||a;return n("ContentSlotsDistributor",{attrs:{"slot-key":t.$parent.slotKey}},[n("h1",[t._v("Events")]),t._v(" "),n("p",[t._v("The majority of events are triggered at the object level through first-party record or element events.")]),t._v(" "),n("h2",{attrs:{id:"objects"}},[n("a",{staticClass:"header-anchor",attrs:{href:"#objects"}},[t._v("#")]),t._v(" Objects")]),t._v(" "),n("ul",[n("li",[n("a",{attrs:{href:"https://docs.craftcms.com/api/v3/craft-base-element.html#events",target:"_blank",rel:"noopener noreferrer"}},[t._v("Organization Element"),n("OutboundLink")],1)]),t._v(" "),n("li",[n("a",{attrs:{href:"https://www.yiiframework.com/doc/api/2.0/yii-db-activerecord#events",target:"_blank",rel:"noopener noreferrer"}},[t._v("Organization Type"),n("OutboundLink")],1)]),t._v(" "),n("li",[n("a",{attrs:{href:"https://www.yiiframework.com/doc/api/2.0/yii-db-activerecord#events",target:"_blank",rel:"noopener noreferrer"}},[t._v("Organization Type Site Settings"),n("OutboundLink")],1)]),t._v(" "),n("li",[n("a",{attrs:{href:"https://www.yiiframework.com/doc/api/2.0/yii-base-model#events",target:"_blank",rel:"noopener noreferrer"}},[t._v("Settings"),n("OutboundLink")],1)]),t._v(" "),n("li",[n("a",{attrs:{href:"https://www.yiiframework.com/doc/api/2.0/yii-db-activerecord#events",target:"_blank",rel:"noopener noreferrer"}},[t._v("User Type"),n("OutboundLink")],1)])]),t._v(" "),n("h2",{attrs:{id:"views"}},[n("a",{staticClass:"header-anchor",attrs:{href:"#views"}},[t._v("#")]),t._v(" Views")]),t._v(" "),n("h3",{attrs:{id:"organization-actions"}},[n("a",{staticClass:"header-anchor",attrs:{href:"#organization-actions"}},[t._v("#")]),t._v(" Organization Actions")]),t._v(" "),n("h4",{attrs:{id:"event-register-organization-actions"}},[n("a",{staticClass:"header-anchor",attrs:{href:"#event-register-organization-actions"}},[t._v("#")]),t._v(" "),n("code",[t._v("EVENT_REGISTER_ORGANIZATION_ACTIONS")])]),t._v(" "),n("p",[t._v("Triggered when available actions are being registered on the organization detail view")]),t._v(" "),n("div",{staticClass:"language-php extra-class"},[n("pre",{pre:!0,attrs:{class:"language-php"}},[n("code",[t._v("    Event"),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(":")]),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(":")]),n("span",{pre:!0,attrs:{class:"token function"}},[t._v("on")]),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),t._v("\n        \\"),n("span",{pre:!0,attrs:{class:"token package"}},[t._v("flipbox"),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("organizations"),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("cp"),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("controllers"),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("view"),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("OrganizationsController")]),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(":")]),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(":")]),n("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("class")]),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n        \\"),n("span",{pre:!0,attrs:{class:"token package"}},[t._v("flipbox"),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("organizations"),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("cp"),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("controllers"),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("view"),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("OrganizationsController")]),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(":")]),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(":")]),n("span",{pre:!0,attrs:{class:"token constant"}},[t._v("EVENT_REGISTER_ORGANIZATION_ACTIONS")]),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n        "),n("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("function")]),t._v(" "),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),t._v("\\"),n("span",{pre:!0,attrs:{class:"token package"}},[t._v("flipbox"),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("organizations"),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("events"),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("RegisterOrganizationActionsEvent")]),t._v(" "),n("span",{pre:!0,attrs:{class:"token variable"}},[t._v("$e")]),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),t._v(" "),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n            "),n("span",{pre:!0,attrs:{class:"token comment"}},[t._v("// Manage `$e->destructiveActions` and `$e->miscActions`")]),t._v("\n    "),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),n("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n")])])])])}),[],!1,null,null,null);a.default=s.exports}}]);