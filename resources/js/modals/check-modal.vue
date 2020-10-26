<template>
  <div>
    <b-modal
      id="check-modal"
      ref="modal"
      :title="modalTitle"
      :ok-title="modalTitle"
      cancel-title="დახურვა"
      @show="showModal"
      @hidden="hideModal"
      @ok="handleOk"
    >
      <form ref="form" @submit.stop.prevent="handleSubmit">
        <b-form-group
          label="სახელი"
          label-for="name-input"
        >
          <b-form-input
            id="name-input"
            v-model="tmpItem.name"
            required
          />
        </b-form-group>
      </form>
    </b-modal>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'CheckModal',

  props: ['item', 'title'],

  data () {
    return {
    }
  },

  computed: {

    modalTitle: function () {
      return this.tmpItem.id ? 'რედაქტირება' : 'დამატება'
    },

    tmpItem: function () {
      return this.item ? this.item : this.getEmptyItem()
    }
  },

  methods: {
    getEmptyItem () {
      return {
        id: null,
        name: ''
      }
    },

    showModal () {
      this.$emit('show-modal')
    },

    hideModal () {
      this.$emit('hide-modal')
    },

    handleOk (event) {
      event.preventDefault()

      this.handleSubmit()
    },

    handleSubmit () {
      if (this.tmpItem.id) {
        this.update()
      } else {
        this.create()
      }
    },

    update () {
      axios.patch('/api/checks/' + this.tmpItem.id, this.tmpItem)
        .then(response => {
          this.$emit('check-updated', response.data.data)

          this.$bvModal.hide('check-modal')
        })
    },

    create () {
      axios.post('/api/checks', this.tmpItem)
        .then(response => {
          this.$emit('check-created', response.data.data)

          this.$bvModal.hide('check-modal')
        })
    }
  }
}
</script>
