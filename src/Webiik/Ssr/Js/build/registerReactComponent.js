var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
import { renderToString } from 'react-dom/server';
import * as ReactDOM from 'react-dom';
import React from "react";
window.WebiikReact = {};
function registerReactComponent(components) {
    Object.keys(components).forEach(function (name) {
        var Component = components[name];
        window.WebiikReact[name] = function (server, uid, props) {
            if (server == 'server') {
                var html = '<div id="' + uid + '">';
                html += renderToString(React.createElement(Component, __assign({}, props)));
                html += '</div>';
                return html;
            }
            if (server == 'server-client') {
                var el = document.getElementById(uid);
                ReactDOM.hydrate(React.createElement(Component, __assign({}, props)), el);
            }
            if (server == 'client') {
                var el = document.getElementById(uid);
                ReactDOM.render(React.createElement(Component, __assign({}, props)), el);
            }
        };
    });
}
export { registerReactComponent };
