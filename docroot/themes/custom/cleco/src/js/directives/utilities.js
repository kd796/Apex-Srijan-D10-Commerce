export const target = function(vnode, binding, listenTypes, fn) {
    const allListenTypes = {
        click: true,
        hover: true,
        focus: true,
    }
    const targets = Object.keys(binding.modifiers || {}).filter(t => !allListenTypes[t])

    if (binding.value) {
        targets.push(binding.value)
    }

    const listener = () => {
        fn({targets, vnode})
    }

    Object.keys(allListenTypes).forEach(type => {
        if (listenTypes[type] || binding.modifiers[type]) {
            vnode.elm.addEventListener(type, listener)
            const boundListeners = vnode.elm.__boundListeners || {}
            boundListeners[type] = boundListeners[type] || []
            boundListeners[type].push(listener)
            vnode.elm.__boundListeners = boundListeners
        }
    })

    // Return the list of targets
    return targets
}
