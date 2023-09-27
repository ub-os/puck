
class MixinBuilderTS {

    constructor(public superclass:any) {
    }
    with(...mixins:any) {
        return mixins.reduce((c, mixin) => mixin(c), this.superclass);
    }
}
const mix = (superclass:any) => new MixinBuilderTS(superclass);

export default mix;