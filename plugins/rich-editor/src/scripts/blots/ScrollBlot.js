import Scroll from "quill/blots/scroll";

export default class ScrollBlot extends Scroll {
    deleteAt(index, length) {
        let [first, offset] = this.line(index);
        let [last] = this.line(index + length);
        // console.log("First", first);
        // console.log("Last", last);
        // console.log("Offset", offset);
        super.deleteAt(index, length);
    }
}
