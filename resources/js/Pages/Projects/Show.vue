<script setup>
import { useForm, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
const props = defineProps({
    project: Object,
});

const taskForm = useForm({
    title: "",
    assigned_to: "",
    due_date: "",
    status: "pending",
});

const submitTask = () => {
    taskForm.post(route("projects.tasks.store", props.project.id), {
        onSuccess: () => taskForm.reset(),
    });
};

const updateStatus = (taskId, advance = true) => {
    router.post(
        route("tasks.updateStatus", taskId),
        { advance },
        { preserveScroll: true }
    );
};

const commentForms = {};
const getCommentForm = (taskId) => {
    if (!commentForms[taskId]) {
        commentForms[taskId] = useForm({ comment: "" });
    }
    return commentForms[taskId];
};
const submitComment = (taskId) => {
    const form = getCommentForm(taskId);
    form.post(route("tasks.comments.store", taskId), {
        onSuccess: () => form.reset(),
        preserveScroll: true,
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

const deleteTask = (id) => {
    if (confirm("Are you sure you want to delete this task?")) {
        router.delete(route("tasks.destroy", id), { preserveScroll: true });
    }
};

</script>

<template>
    <AuthenticatedLayout>
        <div class="p-6 max-w-4xl mx-auto">
            <!-- Project Details -->
            <Link :href="route('projects.index')" class="text-blue-600 hover:underline text-sm">&larr; Back to Projects
            </Link>
            <h1 class="text-2xl font-bold mb-2"><span class="text-xs text-gray-600 uppercase">project name: </span>{{
                project.name }}</h1>
            <p class="mb-1"><span class="text-xs text-gray-600 uppercase">description: </span>{{ project.description }}
            </p>
            <p class="text-sm mb-4">
                Owner: {{ project.owner?.name || "N/A" }} |
                {{ formatDate(project.start_date) }} → {{ formatDate(project.end_date) }}
            </p>

            <!-- Task Creation Form -->
            <div class="p-4 border mb-6">
                <h2 class="font-semibold mb-2">Add Task</h2>
                <form @submit.prevent="submitTask" class="space-y-3">
                    <div>
                        <label class="block mb-1">Title</label>
                        <input v-model="taskForm.title" type="text" class="border px-2 py-1 w-full" />
                        <div v-if="taskForm.errors.title" class="text-red-600 text-sm">{{ taskForm.errors.title }}</div>
                    </div>

                    <div>
                        <label class="block mb-1">Assign to (User ID)</label>
                        <input v-model="taskForm.assigned_to" type="number" class="border px-2 py-1 w-full" />
                        <div v-if="taskForm.errors.assigned_to" class="text-red-600 text-sm">{{
                            taskForm.errors.assigned_to }}</div>
                    </div>

                    <div>
                        <label class="block mb-1">Due Date</label>
                        <input v-model="taskForm.due_date" type="date" class="border px-2 py-1 w-full" />
                        <div v-if="taskForm.errors.due_date" class="text-red-600 text-sm">{{ taskForm.errors.due_date }}
                        </div>
                    </div>

                    <button type="submit" class="bg-gray-500 text-black px-4 py-2 mt-2" :disabled="taskForm.processing">
                        {{ taskForm.processing ? "Adding..." : "Add Task" }}
                    </button>
                </form>
            </div>

            <!-- Task List -->
            <h2 class="text-xl font-bold mb-2">Tasks</h2>
            <div v-if="project.tasks.length === 0" class="text-gray-600">No tasks yet.</div>

            <table v-else class="w-full border border-collapse">
                <thead>
                    <tr class="bg-gray-200 text-left">
                        <th class="border px-2 py-1">Title</th>
                        <th class="border px-2 py-1">Assigned To</th>
                        <th class="border px-2 py-1">Due Date</th>
                        <th class="border px-2 py-1">Status</th>
                        <th class="border px-2 py-1">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="task in project.tasks" :key="task.id">
                        <td class="border px-2 py-1 align-top">
                            <div>{{ task.title }}</div>

                            <!-- Comments -->
                            <div class="mt-2">
                                <strong>Comments:</strong>
                                <ul class="ml-4 list-disc text-sm">
                                    <li v-for="c in task.comments" :key="c.id">
                                        <span class="font-semibold">{{ c.user?.name }}:</span> {{ c.comment }}
                                    </li>
                                </ul>

                                <!-- Add Comment Form -->
                                <form @submit.prevent="submitComment(task.id)" class="mt-1">
                                    <input v-model="getCommentForm(task.id).comment" type="text"
                                        placeholder="Write a comment" class="border px-2 py-1 text-sm w-3/4" />
                                    <button type="submit" class="bg-gray-300 px-2 py-1 text-sm ml-1">
                                        Add
                                    </button>
                                </form>
                                <div v-if="getCommentForm(task.id).errors.comment" class="text-red-600 text-xs">
                                    {{ getCommentForm(task.id).errors.comment }}
                                </div>
                            </div>
                        </td>

                        <td class="border px-2 py-1">{{ task.assigned_to?.name || "Unassigned" }}</td>
                        <td class="border px-2 py-1">{{ formatDate(task.due_date) }}</td>
                        <td class="border px-2 py-1">{{ task.status }}</td>
                        <td class="border px-2 py-1">
                            <button v-if="task.status !== 'completed'" @click="updateStatus(task.id, true)"
                                class="bg-gray-500 text-black px-2 py-1 text-sm">
                                Change Status
                            </button>
                            <button @click="deleteTask(task.id)" class="bg-red-500 text-white px-2 py-1 text-sm">
                                Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AuthenticatedLayout>
</template>
