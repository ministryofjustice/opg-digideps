export default function promiseMiddleware() {
    return (next) => (action) => {
        const { promise, types, ...rest } = action;
        if (!promise) {
            return next(action);
        }

        if (types instanceof Array) {
            switch (types.length) {
            case 1:
                return promise.then(
                    (result) => next({ ...rest, payload: result, type: types[0] })
                );
            case 2:
                return promise.then(
                    (result) => next({ ...rest, payload: result, type: types[0] })
                ).catch(
                    (error) => next({ ...rest, error, type: types[1] })
                );
            default: {
                const [REQUEST, SUCCESS, FAILURE] = types;
                next({ ...rest, type: REQUEST });
                return promise.then(
                    (result) => next({ ...rest, payload: result, type: SUCCESS })
                ).catch(
                    (error) => next({ ...rest, error, type: FAILURE })
                );
            }}
        } else {
            return promise.then(
                (result) => next({ ...rest, payload: result, type: types })
            );
        }
    };
}
