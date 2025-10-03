<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref } from "vue";
import { router, Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
    projects: Array,
    filters: Object,
});

const userId = ref("");

const goToUserTasks = () => {
    if (userId.value) {
        router.get(route("users.tasks", userId.value));
    }
};

const search = ref(props.filters.search || "");

let timeout = null;
const updateSearch = () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(
            route("projects.index"),
            { search: search.value },
            { preserveState: true, replace: true }
        );
    }, 400);
};

const form = useForm({
    name: "",
    description: "",
    start_date: "",
    end_date: "",
    user_id: "",
});

const submit = () => {
    form.post(route("projects.store"), {
        onSuccess: () => {
            form.reset();
        },
    });
};

const formatDate = (date) => {
    if (!date) return "N/A";
    return new Date(date).toLocaleDateString("en-US", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
};

const deleteProject = (id) => {
    if (confirm("Are you sure you want to delete this project?")) {
        router.delete(route("projects.destroy", id));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-6 max-w-5xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Projects</h1>

                <form @submit.prevent="goToUserTasks" class="flex items-center space-x-2">
                    <input v-model="userId" type="number" placeholder="Enter User ID"
                        class="border px-2 py-1 text-sm" />
                    <button type="submit" class="bg-gray-500 text-white px-3 py-1 text-sm">
                        View Tasks
                    </button>
                </form>
            </div>

            <input v-model="search" @input="updateSearch" type="text" placeholder="Search projects..."
                class="border px-3 py-2 mb-4 w-full" />

            <div class="p-4 mb-6 border bg-gray-50">
                <h2 class="font-semibold mb-3">Create New Project</h2>
                <form @submit.prevent="submit" class="space-y-3">
                    <div>
                        <input v-model="form.name" type="text" placeholder="Project name"
                            class="border px-3 py-2 w-full" />
                        <div v-if="form.errors.name" class="text-red-500 text-sm">
                            {{ form.errors.name }}
                        </div>
                    </div>
                    <div>
                        <textarea v-model="form.description" placeholder="Description"
                            class="border px-3 py-2 w-full"></textarea>
                        <div v-if="form.errors.description" class="text-red-500 text-sm">
                            {{ form.errors.description }}
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <div class="flex-1">
                            <label class="text-xs">Start Date</label>
                            <input v-model="form.start_date" type="date" class="border px-3 py-2 w-full" />
                            <div v-if="form.errors.start_date" class="text-red-500 text-sm">
                                {{ form.errors.start_date }}
                            </div>
                        </div>
                        <div class="flex-1">
                            <label class="text-xs">End Date</label>
                            <input v-model="form.end_date" type="date" class="border px-3 py-2 w-full" />
                            <div v-if="form.errors.end_date" class="text-red-500 text-sm">
                                {{ form.errors.end_date }}
                            </div>
                        </div>
                    </div>
                    <div>
                        <input v-model="form.user_id" type="number" placeholder="Owner User ID"
                            class="border px-3 py-2 w-full" />
                        <div v-if="form.errors.user_id" class="text-red-500 text-sm">
                            {{ form.errors.user_id }}
                        </div>
                    </div>
                    <button type="submit" class="bg-gray-500 text-black px-4 py-2 hover:bg-gray-700 disabled:opacity-50"
                        :disabled="form.processing">
                        {{ form.processing ? "Saving..." : "Save Project" }}
                    </button>
                </form>
            </div>

            <div class="space-y-4">
                <div v-for="project in projects" :key="project.id" class="p-4 border shadow-sm bg-white">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold">
                            <span class="text-xs text-gray-600 uppercase">project name: </span>{{ project.name }}
                        </h2>
                        <div class="space-x-2">
                            <Link :href="route('projects.show', project.id)"
                                class="text-blue-600 hover:underline text-sm">
                            Click to View
                            </Link>
                            <button @click="deleteProject(project.id)" class="text-red-600 hover:underline text-sm">
                                Delete
                            </button>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600">
                        <span class="text-xs text-gray-600 uppercase">description: </span>{{ project.description }}
                    </p>
                    <div class="mt-2 text-sm">
                        <span class="font-medium">Owner:</span>
                        {{ project.owner?.name || "N/A" }}
                    </div>
                    <div class="mt-1 text-xs text-gray-500">
                        Total Tasks: {{ project.task_count }} | Completed:
                        {{ project.completed_count }}
                    </div>
                    <div class="mt-1 text-xs text-gray-500">
                        {{ formatDate(project.start_date) }} →
                        {{ formatDate(project.end_date) }}
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
