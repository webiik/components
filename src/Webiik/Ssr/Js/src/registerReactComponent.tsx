import {renderToString} from 'react-dom/server';
import * as ReactDOM from 'react-dom';
import React from "react";

declare global {
    interface Window {
        WebiikReact: any;
    }
}

window.WebiikReact = {};

function registerReactComponent(components: { [id: string]: any }) {
    Object.keys(components).forEach(name => {
        const Component = components[name];

        window.WebiikReact[name] = (server: string, uid: string, props?: any) => {
            if (server == 'server') {
                let html = '<div id="' + uid + '">';
                html += renderToString(<Component {...props}/>);
                html += '</div>';
                return html;
            }

            if (server == 'server-client') {
                const el = document.getElementById(uid);
                ReactDOM.hydrate(<Component {...props}/>, el);
            }

            if (server == 'client') {
                const el = document.getElementById(uid);
                ReactDOM.render(<Component {...props}/>, el);
            }
        };
    });
}

export {registerReactComponent};