class NumberParser {
    constructor(value) {
        this.raw = value !== null ? String(value) : null;

        this.fractionPattern = '([\\d]+)[/]([\\d]+)';
        this.compoundFractionPattern = `([\\d]+)[- ](${this.fractionPattern})`;
        this.numberPattern = /[\d]+(?:[,][\d]+)?(?:[.][\d]+)?/;
        this.numberLetterPattern = /([A-Za-z]+[\d]+([A-Za-z]+)?|([A-Za-z]+)?[\d]+[A-Za-z]+)/;
        this.dimensionsPattern = /([\d.]+)[ ]?(?:mm|in|["'])?(?:[ ]+)?[xX](?:[ ]+)?([\d.]+)[ ]?(?:mm|in|["'])?/;
        this.soloNumberPattern = /[A-Za-z]+[ ]+[\d,.]+([A-Za-z ]+)?/;
    }

    parse() {
        if (this.raw === null) {
            return null;
        }

        return this.parseDimensions()
            || this.parseSoloNumber()
            || this.parseNumberLetter()
            || this.parseCompoundFraction()
            || this.parseFraction()
            || this.parseNumber()
            || null;
    }

    static sort(a, b) {
        let aValue = (new NumberParser(a)).parse();
        let bValue = (new NumberParser(b)).parse();

        // Sort numbers before non-numbers, always
        if (typeof aValue === "number" && typeof bValue !== "number") {
            return -1;
        } else if (typeof bValue === "number" && typeof aValue !== "number") {
            return 1;
        }

        return (aValue > bValue) ? 1 : -1;
    }

    parseCompoundFraction() {
        let matches = this.matchPattern(this.compoundFractionPattern);
        if (matches !== null) {
            let full, whole, fraction, numerator, denomenator;
            [full, whole, fraction, numerator, denomenator] = matches;

            return Number(whole) + (Number(numerator) / Number(denomenator));
        }

        return null;
    }

    parseFraction() {
        let matches = this.matchPattern(this.fractionPattern);
        if (matches !== null) {
            let full, numerator, denomenator;
            [full, numerator, denomenator] = matches;

            return Number(numerator) / Number(denomenator);
        }

        return null;
    }

    parseNumber() {
        let value = this.raw.replace(/,/g, '');
        let matches = this.matchPattern(this.numberPattern, value);
        if (matches !== null) {
            let number;
            [number] = matches;

            return Number(number);
        }
    }

    parseNumberLetter() {
        let matches = this.matchPattern(this.numberLetterPattern);
        if (matches !== null) {
            return this.raw;
        }
    }

    parseDimensions() {
        let matches = this.matchPattern(this.dimensionsPattern);
        if (matches !== null) {
            let full, x, y;
            [full, x, y] = matches;

            return Number(x);
        }
    }

    parseSoloNumber() {
        let matches = this.matchPattern(this.soloNumberPattern);
        if (matches !== null) {
            return this.raw;
        }
    }

    matchPattern(pattern, value = null) {
        value = (value === null) ? this.raw : value;
        pattern = new RegExp(pattern);

        return value.match(pattern);
    }
}

export default NumberParser;

// console.table([
//     '1"',
//     '1-1/2"',
//     '1/2"',
//     '1/4 in',
//     '3 mm',
//     '#5',
//     '9 x 12',
//     'Ø 20',
//     '3/8" Square Drive',
//     'A2 Series',
//     'C3N Series',
//     '2S Series',
//     'Series C7',
//     '1 1/4 in Keyed Spindle',
//     '-01',
//     '-01M6',
//     '-36M3',
//     '13/16"',
//     '1 13/16 in',
//     '1-3/16"',
//     '1.5',
//     '29.8375',
//     '1,234.56',
//     '22x1',
//     '9X1',
//     '23 X 1.25',
//     '1.25x23',
//     '1" x 1.5"',
//     '1mm x 2mm',
//     '1 Series',
//     '2 Series',
//     'Livewire 3 Series',
//     'Check 4',
// ].map((item) => {
//     let parser = new NumberParser(item);
//     return {
//         value: item,
//         parsed: parser.parse(),
//     };
// }));
