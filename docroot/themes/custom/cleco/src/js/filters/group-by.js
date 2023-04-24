export const groupBy = (values, path) => {
    if (Array.isArray(values)) {
        let grouped = {};
        values.forEach((value) => {
            try {
                let group = path.split('.').reduce((o, i) => o[i], value);
                if (typeof grouped[group] === "undefined") {
                    grouped[group] = [];
                }

                grouped[group].push(value);
            } catch (error) {
                console.warn(`Group path ${path} is not valid`);
            }
        });

        return grouped;
    }

    return values;
};

export default groupBy;
