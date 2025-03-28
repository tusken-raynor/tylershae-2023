const actionMap = new Map<string, Array<() => void>>();

export default {
  on(name: string, ...callbacks: Array<() => void>) {
    let callbackList = actionMap.get(name);
    if (!callbackList) {
      callbackList = [];
      actionMap.set(name, callbackList);
    }
    callbackList.push(...callbacks);
  },
  do(name: string) {
    const callbacks = actionMap.get(name);
    if (callbacks) {
      for (let i = 0; i < callbacks.length; i++) {
        const callback = callbacks[i];
        callback();
      }
    }
  },
};
