# medas-pdo-storage

Part of the [Medas framework](https://github.com/tarantuli/medas-core).

    private function getForeignKeyName(Blueprint\ForeignKey $foreignKey): string
    {
        $keyHash = sha1(json_encode([$this->name, $foreignKey->field, $foreignKey->foreignEntity, $foreignKey->foreignField]));

        return sprintf('mfk_%s', $keyHash);
    }

