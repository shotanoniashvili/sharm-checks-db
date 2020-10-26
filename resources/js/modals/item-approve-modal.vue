<template>
  <div>
    <b-modal
      id="item-approve-modal"
      ref="modal"
      title="ჩეკის კომპონენტის დადასტურება/უარყოფა"
      ok-disabled
      cancel-disabled
      size="lg"
      @shown="shownModal"
      @hidden="hideModal"
    >
      <form ref="form">
        <b-form-group
          label="კომენტარი"
          label-for="comment-input"
        >
          <b-textarea
            id="comment-input"
            v-model="comment"
          />
        </b-form-group>
      </form>

      <template #modal-footer>
        <div class="w-100">
          <div class="float-left">
            <b-button
              variant="success"
              @click="approve()"
            >
              დადასტურება
            </b-button>
            <b-button
              variant="danger"
              @click="reject()"
            >
              უარყოფა
            </b-button>
          </div>
          <b-button
            variant="secondary"
            class="float-right"
            @click="$bvModal.hide('item-approve-modal')"
          >
            დახურვა
          </b-button>
        </div>
      </template>
    </b-modal>
  </div>
</template>

<script>
import axios from 'axios'
import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css'

export default {
  name: 'ItemApproveModal',

  components: {
    vSelect
  },

  props: ['check', 'item', 'manager'],

  data () {
    return {
      comment: ''
    }
  },

  computed: {

  },

  methods: {
    approve () {
      axios.post('/api/checks/' + this.check.id + '/items/' + this.item.id + '/approve/' + this.manager.id, {
        comment: this.comment
      })
        .then(response => {
          this.$bvModal.hide('item-approve-modal')
        })
    },

    reject () {
      axios.post('/api/checks/' + this.check.id + '/items/' + this.item.id + '/reject/' + this.manager.id, {
        comment: this.comment
      })
        .then(response => {
            this.$bvModal.hide('item-approve-modal')
        })
    },

    shownModal () {
      this.loadStatus()

      this.$emit('shown-modal')
    },

    hideModal () {
      this.comment = ''
      this.$emit('hide-modal')
    },

    loadStatus () {
      axios.get('/api/checks/' + this.check.id + '/items/' + this.item.id + '/' + this.manager.id)
        .then(response => {
          if (response.data.data) this.comment = response.data.data.comment
        })
    }

  }
}
</script>
