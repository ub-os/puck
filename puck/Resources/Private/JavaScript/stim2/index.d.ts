type TraitConstructor = { new (...args: ConstructorParameters<typeof ElementTrait>): ElementTrait }
type TraitConstructorRecord = Record<string, TraitConstructor>
type TraitProps = Record<string, string|number|boolean|object>
type SelectorCallbackRecord = Record<string, (target: HTMLElement) => void>
declare class TraitRef {
	constructor(el: HTMLElement, descriptor: string)
}
declare class TraitHandler {
	constructor(el: HTMLElement, descriptor: string)
}
declare class PropSyncer {
	constructor(props: TraitProps, token: string)
}
type Config = {
    observeChildList: boolean,
    observeAttributes: boolean,
    observeAspectAttributes: boolean,
    attributePrefix: string,
    traitAttribute: string,
	refAttribute: string,
    handlerAttribute: string,
}
declare class Stim {
	config: Config
	get traitRegister(): TraitConstructorRecord
	get selectorRegister(): SelectorCallbackRecord
	get syncers(): Record<string, PropSyncer>
	get traits(): WeakMap<HTMLElement, Record<string, ElementTrait>>
	get refs(): WeakMap<HTMLElement, Record<string, TraitRef>>
	get handlers(): WeakMap<HTMLElement, Record<string, TraitHandler>>
    registerTrait(token: string|TraitConstructorRecord, traitClass?: TraitConstructor): void
    registerSelectorCallback(selector: string|SelectorCallbackRecord, callback?: (target: HTMLElement) => void): void
    connect(): void
    disconnect(): void
    connectElement(element: HTMLElement): void
    disconnectElement(element: HTMLElement): void
}
declare const stim: Stim
declare class ElementTrait {
	static token: string
	static traits: Record<string, TraitProps>
	static props: TraitProps
    static refs: Array<string>
    static registered(token: string, stim: Stim): void
	get token(): string
	get element(): HTMLElement
	get stim(): Stim
	constructor(element: HTMLElement, syncToken: string)
    initialized(): void
    connected(): void
    disconnected(): void
    attributeChanged(name: string, oldValue: string, newValue: string): void
}

export { stim, ElementTrait }