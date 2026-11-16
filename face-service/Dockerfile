FROM node:22-bookworm-slim

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --omit=dev --no-audit --no-fund
COPY models ./models
COPY server.cjs ./server.cjs

USER node
EXPOSE 8001
CMD ["node", "server.cjs"]
