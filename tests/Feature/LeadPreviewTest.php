<?php

namespace Tests\Feature;

use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Tests\TestCase;

class LeadPreviewTest extends TestCase
{
    use RefreshDatabase;

    private array $temporaryPaths = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryPaths as $path) {
            @unlink($path);
        }
        parent::tearDown();
    }

    public function test_page_shows_the_upload_form(): void
    {
        $this->get('/')->assertOk()->assertSee('Smart Orange Test Project');
    }

    public function test_preview_preserves_dates_unicode_and_empty_columns_without_inserting(): void
    {
        $rows = [];
        for ($i = 1; $i <= 7; $i++) {
            $rows[] = ["LD-$i", new DateTimeImmutable('2026-01-02 10:30:00'),
                'Олександр', 'Поліщук', '380671234567', null, 'Київ', 'Website',
                null, 'Сайт', 0, 'new', null, '<script>alert(1)</script>', null];
        }
        $file = $this->xlsx([config('import.headers'), ...$rows]);
        $this->post(route('imports.preview'), ['file' => $file])
            ->assertOk()
            ->assertSee('Олександр')
            ->assertSee('2026-01-02 10:30:00')
            ->assertSee('LD-5')
            ->assertDontSee('LD-6')
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_invalid_headers_are_reported(): void
    {
        $file = $this->xlsx([['wrong_header'], ['LD-1']]);
        $this->from('/')->post(route('imports.preview'), ['file' => $file])
            ->assertRedirect('/')->assertSessionHasErrors('file');
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_non_xlsx_is_rejected(): void
    {
        $this->from('/')->post(route('imports.preview'), [
            'file' => UploadedFile::fake()->create('data.csv', 1, 'text/csv'),
        ])->assertRedirect('/')->assertSessionHasErrors('file');
    }

    private function xlsx(array $rows): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'lead-test-');
        $this->temporaryPaths[] = $path;
        $writer = new Writer;
        $writer->openToFile($path);
        foreach ($rows as $values) {
            $row = Row::fromValues($values);
            if (($values[1] ?? null) instanceof DateTimeImmutable) {
                $row->getCells()[1]->setStyle((new Style)->setFormat('yyyy-mm-dd hh:mm:ss'));
            }
            $writer->addRow($row);
        }
        $writer->close();

        return new UploadedFile($path, 'leads.xlsx', null, null, true);
    }
}
