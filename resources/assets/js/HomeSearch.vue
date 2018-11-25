<template>
    <ais-search-box class="home-search-box">
        <div class="input-group">
            <ais-input
             v-on:input="$emit('query')"
             placeholder="Search..."
             autofocus="true"
             :class-names="{'ais-input': 'form-control form-control-lg'}"
             ></ais-input>

            <div class="input-group-append">
                <ais-clear :class-names="{'ais-clear': 'btn btn-light'}">
                    <span aria-hidden="true">
                        <svg title="Clear" class="octicon" xmlns="http://www.w3.org/2000/svg" width="12" height="16" viewBox="0 0 12 16"><path fill-rule="evenodd" d="M7.48 8l3.75 3.75-1.48 1.48L6 9.48l-3.75 3.75-1.48-1.48L4.52 8 .77 4.25l1.48-1.48L6 6.52l3.75-3.75 1.48 1.48z"/></svg>
                    </span>
                    </ais-clear>
                    <button class="btn btn-primary" type="submit">
                        <span aria-hidden="true">
                            <svg title="Search" class="octicon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15.7 13.3l-3.81-3.83A5.93 5.93 0 0 0 13 6c0-3.31-2.69-6-6-6S1 2.69 1 6s2.69 6 6 6c1.3 0 2.48-.41 3.47-1.11l3.83 3.81c.19.2.45.3.7.3.25 0 .52-.09.7-.3a.996.996 0 0 0 0-1.41v.01zM7 10.7c-2.59 0-4.7-2.11-4.7-4.7 0-2.59 2.11-4.7 4.7-4.7 2.59 0 4.7 2.11 4.7 4.7 0 2.59-2.11 4.7-4.7 4.7z"/></svg>
                        </span>
                    </button>
            </div>
        </div>
    </ais-search-box>
</template>

<script>
export default {
    methods: {
        onQuery: function() {
            alert(1);
        }
    }
}
</script>
