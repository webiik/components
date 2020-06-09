declare global {
    interface Window {
        WebiikReact: any;
    }
}
declare function registerReactComponent(components: {
    [id: string]: any;
}): void;
export { registerReactComponent };
