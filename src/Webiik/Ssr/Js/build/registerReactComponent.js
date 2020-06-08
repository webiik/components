import { renderToString } from 'react-dom/server';
import * as ReactDOM from 'react-dom';
import React from "react";
window.WebiikReact = {};
function registerReactComponent(components) {
    Object.keys(components).forEach(name => {
        const Component = components[name];
        window.WebiikReact[name] = (server, uid, props) => {
            if (server == 'server') {
                let html = '<div id="' + uid + '">';
                html += renderToString(React.createElement(Component, Object.assign({}, props)));
                html += '</div>';
                return html;
            }
            if (server == 'server-client') {
                const el = document.getElementById(uid);
                ReactDOM.hydrate(React.createElement(Component, Object.assign({}, props)), el);
            }
            if (server == 'client') {
                const el = document.getElementById(uid);
                ReactDOM.render(React.createElement(Component, Object.assign({}, props)), el);
            }
        };
    });
}
export { registerReactComponent };
