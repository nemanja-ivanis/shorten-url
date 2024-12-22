<template>
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span>URLs</span>
      <div class="ms-auto">
        <a href="/add-url" class="btn btn-primary">Add new URL</a>
      </div>
    </div>
    <div class="card-body">
      <!-- Table to display URLs -->
      <table class="table table-bordered table-hover rounded" v-if="urls.data.length > 0">
        <thead>
        <tr>
          <th>#</th>
          <th>Short URL</th>
          <th>Short Code</th>
          <th>Real URL</th>
          <th>Visits</th>
          <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="url in urls.data" :key="url.id">
          <td>{{ url.id }}</td>
          <td><a :href="'/r/' + url.short_url" target="_blank">{{mainUrl}}/r/{{ url.short_url }}</a></td>
          <td>{{ url.short_url }}</td>
          <td>{{ url.real_url }}</td>
          <td>{{ url.visits_counter }}</td>
          <td><a :href="'/update-url/' + url.id" class="me-2">Update</a><a href="#" @click.prevent="deleteUrl(url.id)">Delete</a></td>
        </tr>
        </tbody>
      </table>
      <p v-else class="text-center">You haven't created any urls yet.</p>
      <!-- Pagination Controls -->
      <div class="pagination align-items-center" v-if="urls.data.length > 0">
        <button class="btn btn-secondary" @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1">Previous</button>
        <span class="ms-2 me-2">Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
        <button class="btn btn-secondary" @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page">Next</button>
      </div>
    </div>
  </div>
</template>

<script>
import {toast} from "vue3-toastify";
import 'vue3-toastify/dist/index.css';
export default {
  props:['token'],
  data() {
    return {
      urls: {
        data: [],
      },
      pagination: {
        current_page: 1,
        last_page: 1,
      },
      limit: 10,
      mainUrl: window.location.host,
      successMessage: null,
    };
  },
  methods: {
    /**
     * Fetch URLs
     *
     * @param page
     */
    async fetchUrls(page = 1) {
      try {
        const response = await axios.get("/api/urls", {
          params: {
            page: page,
            limit: this.limit,
          },
          headers: {
            Authorization: `Bearer ${this.token}`,
            Accept: "application/json",
          }
        });
        this.urls = response.data;
        this.pagination.current_page = response.data.pagination.current_page;
        this.pagination.last_page = response.data.pagination.last_page;
      } catch (error) {
        console.error("Error fetching URLs", error);
      }
    },
    /**
     * Change page
     *
     * @param page
     */
    changePage(page) {
      if (page > 0 && page <= this.pagination.last_page) {
        this.fetchUrls(page);
      }
    },
    /**
     *  Delete URL
     *
     * @param id
     */
    deleteUrl(id) {
      if (confirm("Are you sure you want to delete this URL?")) {
        axios.delete(`/api/urls/${id}`, {
          headers: {
            Authorization: `Bearer ${this.token}`,
          }
        }).then(() => {
          this.fetchUrls();
          toast("URL deleted successfully!", {
            autoClose: 2000,
            type: "success"
          });
        }).catch(() => {
          toast("Error deleting URL!", {
            autoClose: 2000,
            type: "error"
          });
        });
      }
    }
  },
  /**
   * Fetch URLs when the component is mounted
   */
  mounted() {
    // Check localStorage for the success message
    this.successMessage = localStorage.getItem('successMessage');

    if (this.successMessage) {
      toast(this.successMessage, {
        autoClose: 2000,
        type: "success"
      });
      localStorage.removeItem('successMessage');
    }

    this.fetchUrls();
  },
};
</script>

<style scoped>

</style>