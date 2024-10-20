export function registerComponents(app, components) {
  Object.keys(components).forEach(componentName => {
    app.component(componentName, components[componentName]);
  });
}
