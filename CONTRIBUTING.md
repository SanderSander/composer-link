## Contributing

Feel free to contribute, all help is welcome!

1. Fork it!
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request :D

### Testing

You can run the full test suite, including integrations tests with the following command.

```
docker run --rm --interactive --tty \
  --volume $PWD:/app \
  composer test-integration
```