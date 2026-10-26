using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using OdontoCare.Application.Common;
using OdontoCare.Application.DTOs;
using OdontoCare.Application.Interfaces;
using OdontoCare.Application.Security;

namespace OdontoCare.API.Controllers;

[ApiController]
[Authorize(Roles = Roles.Todos)]
[Route("api/materiais")]
public sealed class MateriaisController(IMaterialService service) : ControllerBase
{
    private int UsuarioId => int.TryParse(User.FindFirst("sub")?.Value, out var id) ? id : 0;

    [HttpGet]
    public async Task<IActionResult> Listar([FromQuery] int pagina = 1, [FromQuery] int tamanho = 20,
        [FromQuery] string? busca = null, [FromQuery] bool? estoqueBaixo = null) =>
        Ok(ResultWrapper<PaginaDto<MaterialDto>>.Ok(await service.ListarAsync(pagina, tamanho, busca, estoqueBaixo)));

    [HttpGet("{id:int}")]
    public async Task<IActionResult> Buscar(int id)
    {
        var material = await service.BuscarAsync(id);
        return material is null ? NotFound(ResultWrapper<object>.NotFound("Material não encontrado.")) :
            Ok(ResultWrapper<MaterialDto>.Ok(material));
    }

    [Authorize(Roles = Roles.Gestao)]
    [HttpPost]
    public async Task<IActionResult> Criar([FromBody] MaterialGravarDto dto)
    {
        var material = await service.CriarAsync(dto);
        return CreatedAtAction(nameof(Buscar), new { id = material.IdMaterial }, ResultWrapper<MaterialDto>.Ok(material, "Material cadastrado."));
    }

    [Authorize(Roles = Roles.Gestao)]
    [HttpPut("{id:int}")]
    public async Task<IActionResult> Atualizar(int id, [FromBody] MaterialGravarDto dto)
    {
        var material = await service.AtualizarAsync(id, dto);
        return material is null ? NotFound(ResultWrapper<object>.NotFound("Material não encontrado.")) :
            Ok(ResultWrapper<MaterialDto>.Ok(material, "Material atualizado."));
    }

    [Authorize(Roles = Roles.Administrador)]
    [HttpDelete("{id:int}")]
    public async Task<IActionResult> Excluir(int id) => await service.ExcluirAsync(id) ?
        Ok(ResultWrapper<object>.Ok(null, "Material excluído.")) :
        NotFound(ResultWrapper<object>.NotFound("Material não encontrado."));

    [Authorize(Roles = Roles.Gestao)]
    [HttpPost("{id:int}/entradas")]
    public Task<IActionResult> Entrada(int id, [FromBody] MovimentacaoCreateDto dto) => Mover(id, "Entrada", dto.Quantidade);

    [Authorize(Roles = Roles.Gestao)]
    [HttpPost("{id:int}/saidas")]
    public Task<IActionResult> Saida(int id, [FromBody] MovimentacaoCreateDto dto) => Mover(id, "Saida", dto.Quantidade);

    private async Task<IActionResult> Mover(int id, string tipo, int quantidade)
    {
        var material = await service.MovimentarAsync(id, tipo, quantidade, UsuarioId);
        return material is null ? NotFound(ResultWrapper<object>.NotFound("Material não encontrado.")) :
            Ok(ResultWrapper<MaterialDto>.Ok(material, "Movimentação registrada."));
    }

    [HttpGet("{id:int}/movimentacoes")]
    public async Task<IActionResult> Historico(int id)
    {
        if (await service.BuscarAsync(id) is null)
            return NotFound(ResultWrapper<object>.NotFound("Material não encontrado."));
        return Ok(ResultWrapper<IReadOnlyList<MovimentacaoDto>>.Ok(await service.HistoricoAsync(id)));
    }
}
