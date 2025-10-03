<script setup>
import { Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    tasks: Array,
    user_id: Number,
});

const formatDate = (date) => {
    if (!date) return "N/A";
    return new Date(date).toLocaleDateString("en-US", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-6 max-w-5xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">
                    Tasks Assigned to User #{{ user_id }}
                </h1>
                <Link :href="route('projects.index')" class="text-blue-600 underline text-sm">
                ← Back to Projects
                </Link>
            </div>

            <div v-if="tasks.length === 0" class="text-gray-600">
                No tasks found for this user.
            </div>

            <table v-else class="w-full border border-collapse">
                <thead>
                    <tr class="bg-gray-200 text-left">
                        <th class="border px-2 py-1">Title</th>
                        <th class="border px-2 py-1">Project</th>
                        <th class="border px-2 py-1">Due Date</th>
                        <th class="border px-2 py-1">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="task in tasks" :key="task.id">
                        <td class="border px-2 py-1">{{ task.title }}</td>
                        <td class="border px-2 py-1">
                            <Link :href="route('projects.show', task.project_id)" class="text-blue-600 underline">
                            {{ task.project_name }}
                            </Link>
                        </td>
                        <td class="border px-2 py-1">{{ formatDate(task.due_date) }}</td>
                        <td class="border px-2 py-1">{{ task.status }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AuthenticatedLayout>
</template>
