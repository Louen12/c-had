FROM node:20-alpine

WORKDIR /srv/app

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint && chmod +x /usr/local/bin/entrypoint

EXPOSE 3000

ENTRYPOINT ["entrypoint"]
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
