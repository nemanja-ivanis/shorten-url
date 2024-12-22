<template>
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span v-if="!id">Add URL</span><span v-else>Update URL - {{shortCode}}</span>
    </div>
    <div class="card-body">
      <form @submit.prevent="submitUrl">
        <div class="mb-3">
          <label for="real_url" class="form-label">Real URL</label>
          <input type="url" class="form-control" id="real_url" aria-describedby="real_url" placeholder="Enter real URL" v-model="realUrl" required>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
      </form>
    </div>
  </div>
</template>
<script>
import {toast} from "vue3-toastify";

export default {
  props: ['token', 'id'],
  data() {
    return {
        realUrl: '',
        shortCode: ''
    };
  },
  mounted() {
    this.getUrl();
  },
  methods: {
    /**
     * Get URL by ID
     *
     * @returns {Promise<void>}
     */
    getUrl() {
      if(this.id) {
        axios.get(`/api/urls/${this.id}`, {
          headers: {
            'Authorization': `Bearer ${this.token}`,
          }
        }).then(response => {
          this.realUrl = response.data.data.real_url;
          this.shortCode = response.data.data.short_url;
        }).catch(error => {
          toast("Server error!", {
            autoClose: 2000,
            type: "error"
          });
        });
      }
    },
    /**
     *  Submit URL to the backend
     *
     * @returns {Promise<void>}
     */
    async submitUrl() {
      try {
        // Prepare data to send
        const data = {
          real_url: this.realUrl
        };

        let msg = '';
        let response = null;
        if(this.id) {
          // Send PATCH request to the backend with Authorization header
          response = await axios.patch('/api/urls/' + this.id, data, {
            headers: {
              'Authorization': `Bearer ${this.token}`,
            }
          });
          msg = 'URL successfully updated!';
        }else{
          // Send POST request to the backend with Authorization header
          response = await axios.post('/api/urls', data, {
            headers: {
              'Authorization': `Bearer ${this.token}`,
            }
          });
          msg = 'URL successfully added!';
        }

        if(!this.id) {
          this.realUrl = '';

        }

        if(response.data.success){

          // Store the success message in localStorage
          localStorage.setItem('successMessage', msg);

          // Redirect to the /home page
          window.location.href = '/home';
        }

      } catch (error) {
        console.log(error)
        // Handle errors
        if (error.response.data.errors.length > 0) {
          toast("Validation error!", {
            autoClose: 2000,
            type: "error"
          });
        } else {
          toast("Server error!", {
            autoClose: 2000,
            type: "error"
          });
        }
      }
    }
  }
}
</script>