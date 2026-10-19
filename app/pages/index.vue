<template>
  <main class="discover">
    <h1>C'had</h1>
    <p>Qui vend quoi, sur quel marché, et quand ?</p>
  </main>
</template>

<style scoped>
.discover {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100dvh;
  padding: var(--space-xl);
  text-align: center;
}

.discover h1 {
  font-size: var(--text-3xl);
  color: var(--color-brand);
}

.discover p {
  margin-top: var(--space-sm);
  font-size: var(--text-lg);
  color: var(--color-text-secondary);
}
</style>
