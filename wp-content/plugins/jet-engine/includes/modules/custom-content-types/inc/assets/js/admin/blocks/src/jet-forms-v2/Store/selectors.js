export function getTypes( state ) {
	return state?.api?.types ?? {};
}

export function getType( state, slug ) {
	return state?.api?.types?.[ slug ];
}